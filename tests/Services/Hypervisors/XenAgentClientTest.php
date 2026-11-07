<?php

namespace NextDeveloper\IAAS\Tests\Services\Hypervisors;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\AgentEndpoint;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentClient;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException;
use Tests\TestCase;

/**
 * Covers the HTTPS agent client with Http::fake only. The connection-problem accounting is
 * overridden with a no-op so these tests never touch the database (StateHelper writes rows).
 */
class XenAgentClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The dev .env sets SSH_DRY_RUN=true, which makes the client send nothing. Force it
        // off so the requests under test actually go through Http::fake (no real network).
        config(['leo.debug.ssh.dry_run' => false]);
    }

    private function member(array $overrides = []): ComputeMembers
    {
        $member = new ComputeMembers();
        $member->name            = 'test-host';
        $member->ip_addr         = '10.0.0.5/24';
        $member->agent_api_key   = 'secret-token';
        $member->is_alive        = true;
        $member->management_data = ['http_agent' => ['enabled' => true]];

        foreach ($overrides as $key => $value) {
            $member->{$key} = $value;
        }

        return $member;
    }

    private function client(ComputeMembers $member): XenAgentClient
    {
        return new class($member) extends XenAgentClient {
            protected function recordConnectionOk(): void
            {
            }

            protected function recordConnectionProblem(string $operation, string $error): void
            {
            }
        };
    }

    /**
     * Request body as the agent sees it. params is sent as a JSON object (stdClass in PHP),
     * so round-trip through JSON to get plain arrays for assertions.
     */
    private function body(Request $request): array
    {
        return json_decode(json_encode($request->data()), true);
    }

    private function ok(array $output): array
    {
        return $this->reply('completed', $output);
    }

    private function reply(string $status, array $output = [], ?string $message = null): array
    {
        return ['v' => 1, 'type' => 'result', 'payload' => array_filter([
            'command_id' => 'x',
            'status'     => $status,
            'message'    => $message,
            'output'     => $output ?: null,
        ])];
    }

    public function test_sends_envelope_with_bearer_token_to_bare_ip_and_default_port()
    {
        Http::fake(['*' => Http::response($this->reply('completed', ['success' => true, 'data' => ['uuid' => 'u1']]))]);

        $output = $this->client($this->member())->call('xenserver.vm.start', ['uuid' => 'vm-1']);

        $this->assertSame('u1', $output['data']['uuid']);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://10.0.0.5:8443/v1/command'
                && $request->hasHeader('Authorization', 'Bearer secret-token')
                && $this->body($request)['payload']['operation'] === 'xenserver.vm.start'
                && $this->body($request)['payload']['params']['uuid'] === 'vm-1'
                && $this->body($request)['v'] === 1;
        });
    }

    public function test_host_port_override_is_used()
    {
        Http::fake(['*' => Http::response($this->reply('completed', ['ok' => 1]))]);

        $member = $this->member(['management_data' => ['http_agent' => ['enabled' => true, 'port' => 9443]]]);
        $this->client($member)->call('xenserver.vm.list');

        Http::assertSent(fn(Request $request) => str_contains($request->url(), ':9443/'));
    }

    public function test_read_operation_output_is_returned_without_success_key()
    {
        Http::fake(['*' => Http::response($this->reply('completed', [['uuid' => 'a'], ['uuid' => 'b']]))]);

        $output = $this->client($this->member())->call('xenserver.vm.list');

        $this->assertCount(2, $output);
    }

    public function test_output_success_false_throws_with_message()
    {
        Http::fake(['*' => Http::response($this->reply('completed', [
            'success' => false,
            'message' => 'vm is running',
            'detail'  => ['exit_code' => 1, 'stderr' => 'password=hunter2'],
        ]))]);

        try {
            $this->client($this->member())->call('xenserver.vm.destroy', ['uuid' => 'vm-1', 'confirm' => true]);
            $this->fail('Expected XenAgentException');
        } catch (XenAgentException $e) {
            $this->assertStringContainsString('vm is running', $e->getMessage());
            $this->assertSame(1, $e->getSafeContext()['exit_code']);
            // stderr can carry secrets, so the safe context must not expose it.
            $this->assertStringNotContainsString('hunter2', json_encode($e->getSafeContext()));
        }
    }

    public function test_rejected_and_failed_statuses_throw()
    {
        // Http::fake() stubs accumulate (first match wins), so serve both replies from one sequence.
        Http::fake(['*' => Http::sequence()
            ->push($this->reply('rejected', [], 'nope'))
            ->push($this->reply('failed', [], 'nope'))]);

        foreach (['rejected', 'failed'] as $status) {
            try {
                $this->client($this->member())->call('xenserver.vm.start');
                $this->fail('Expected XenAgentException for ' . $status);
            } catch (XenAgentException $e) {
                $this->assertSame($status, $e->getAgentStatus());
            }
        }
    }

    public function test_http_error_without_envelope_throws()
    {
        Http::fake(['*' => Http::response(['error' => 'unauthorized'], 401)]);

        $this->expectException(XenAgentException::class);
        $this->client($this->member())->call('xenserver.vm.list');
    }

    public function test_operation_still_missing_after_refreshing_capabilities_is_rejected_without_running_it()
    {
        // The cached list lacks the operation, so the client re-reads agent.allowed_operations once.
        // The agent's fresh list still lacks it, so the operation itself must never be sent.
        Http::fake(['*' => Http::response($this->ok(['operations' => [['operation' => 'xenserver.vm.list']]]))]);

        $member = $this->member(['available_operations' => ['agent' => [['operation' => 'xenserver.vm.list']]]]);

        try {
            $this->client($member)->call('xenserver.vm.destroy', ['uuid' => 'x']);
            $this->fail('Expected XenAgentException');
        } catch (XenAgentException $e) {
            $this->assertSame('rejected', $e->getAgentStatus());
        }

        $sent = [];
        Http::assertSent(function (Request $request) use (&$sent) {
            $sent[] = json_decode(json_encode($request->data()), true)['payload']['operation'];

            return true;
        });

        $this->assertSame(['agent.allowed_operations'], $sent);
    }

    public function test_stale_capability_cache_is_refreshed_and_the_new_operation_runs()
    {
        // Agent was upgraded after the member was added: the cache lacks the new operation.
        Http::fake(['*' => Http::sequence()
            ->push($this->ok(['operations' => [
                ['operation' => 'xenserver.vm.list'],
                ['operation' => 'xenserver.inventory.snapshot'],
            ]]))
            ->push($this->ok(['pool' => ['host_count' => 1]]))]);

        $member = $this->member(['available_operations' => ['agent' => [['operation' => 'xenserver.vm.list']]]]);

        $output = $this->client($member)->call('xenserver.inventory.snapshot');

        $this->assertSame(1, $output['pool']['host_count']);
        // The refreshed list is kept on the member for the rest of the run.
        $this->assertContains(
            'xenserver.inventory.snapshot',
            array_column($member->available_operations['agent'], 'operation')
        );
    }

    public function test_host_without_enabled_flag_fails_loudly()
    {
        Http::fake();

        $member = $this->member(['management_data' => []]);

        $this->expectException(XenAgentException::class);
        $this->client($member)->call('xenserver.vm.list');
    }

    public function test_legacy_is_management_agent_available_flag_is_not_the_opt_in()
    {
        $member = $this->member(['management_data' => []]);
        $member->is_management_agent_available = true;

        $this->assertFalse(AgentEndpoint::isEnabled($member));
    }

    public function test_async_polls_job_status_until_completed()
    {
        config(['virtualization.agent.async.poll_interval' => 1]);

        Http::fake([
            '*' => Http::sequence()
                ->push($this->reply('accepted', ['job_id' => 'j1', 'status' => 'running']), 202)
                ->push($this->reply('completed', ['status' => 'running']))
                ->push($this->reply('completed', ['status' => 'completed', 'result' => ['success' => true, 'data' => ['uuid' => 'new']]])),
        ]);

        $result = $this->client($this->member())->callAsync('xenserver.vm.clone', ['uuid' => 'vm-1'], 30);

        $this->assertSame('new', $result['data']['uuid']);

        Http::assertSent(fn(Request $request) => ($this->body($request)['payload']['params']['async'] ?? null) === true);
    }

    public function test_async_job_failure_throws()
    {
        config(['virtualization.agent.async.poll_interval' => 1]);

        Http::fake([
            '*' => Http::sequence()
                ->push($this->reply('accepted', ['job_id' => 'j1', 'status' => 'running']), 202)
                ->push($this->reply('completed', ['status' => 'failed', 'message' => 'disk full'])),
        ]);

        $this->expectException(XenAgentException::class);
        $this->client($this->member())->callAsync('xenserver.vm.export', ['uuid' => 'vm-1'], 30);
    }

    public function test_dry_run_sends_nothing()
    {
        Http::fake();
        config(['leo.debug.ssh.dry_run' => true]);

        $output = $this->client($this->member())->call('xenserver.vm.start', ['uuid' => 'vm-1']);

        $this->assertTrue($output['dry_run']);
        Http::assertNothingSent();
    }
}
