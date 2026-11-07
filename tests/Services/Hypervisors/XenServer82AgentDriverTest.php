<?php

namespace NextDeveloper\IAAS\Tests\Services\Hypervisors;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NextDeveloper\IAAS\Contracts\ProvisioningCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\AgentResultMapper;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentClient;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\XenServer82AgentDriver;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\XenServer82SshDriver;
use Tests\TestCase;

/**
 * Driver tests with Http::fake and unsaved models only - nothing here reads or writes the
 * database. client() is replaced with a client that has no connection accounting, and sync()
 * is replaced so the VM row is never updated.
 */
class XenServer82AgentDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Dev .env has SSH_DRY_RUN=true, which would stop the client from sending anything.
        config(['leo.debug.ssh.dry_run' => false]);
    }

    private function vm(): VirtualMachines
    {
        $vm = new VirtualMachines();
        $vm->uuid            = 'vm-db-uuid';
        $vm->hypervisor_uuid = 'xen-vm-1';
        $vm->status          = 'halted';

        return $vm;
    }

    private function driver(): XenServer82AgentDriver
    {
        $member = new ComputeMembers();
        $member->name            = 'test-host';
        $member->ip_addr         = '10.0.0.5/24';
        $member->agent_api_key   = 'secret-token';
        $member->is_alive        = true;
        $member->management_data = ['http_agent' => ['enabled' => true]];

        return new class($member) extends XenServer82AgentDriver {
            public function __construct(private readonly ComputeMembers $member)
            {
                parent::__construct();
            }

            protected function client(VirtualMachines $vm): XenAgentClient
            {
                return new class($this->member) extends XenAgentClient {
                    protected function recordConnectionOk(): void
                    {
                    }

                    protected function recordConnectionProblem(string $operation, string $error): void
                    {
                    }
                };
            }

            public function sync(VirtualMachines $vm): VirtualMachines
            {
                return $vm;
            }
        };
    }

    private function ok(array $output = ['success' => true]): array
    {
        return ['v' => 1, 'type' => 'result', 'payload' => ['command_id' => 'x', 'status' => 'completed', 'output' => $output]];
    }

    private function sentOperations(): array
    {
        $operations = [];

        Http::assertSent(function (Request $request) use (&$operations) {
            $operations[] = json_decode(json_encode($request->data()), true)['payload'];

            return true;
        });

        return $operations;
    }

    public function test_agent_platform_keys_resolve_to_agent_driver_and_ssh_keys_stay_on_ssh()
    {
        $manager = app(VirtualMachineManager::class);

        $this->assertInstanceOf(XenServer82AgentDriver::class, $manager->resolveDriver('xenserver-8.2-agent'));
        $this->assertInstanceOf(XenServer82AgentDriver::class, $manager->resolveDriver('xcp-ng-8.2-agent'));

        // Regression guard: every value that exists in production today must still be SSH.
        foreach (['xenserver-8.2', 'xenserver-8.2-ssh', 'xcp-ng-8.2', 'xcp-ng-8.2-ssh'] as $platform) {
            $this->assertInstanceOf(XenServer82SshDriver::class, $manager->resolveDriver($platform));
        }
    }

    public function test_agent_driver_declares_only_the_capabilities_it_implements()
    {
        $driver = $this->driver();

        $this->assertInstanceOf(VirtualMachineAdapterInterface::class, $driver);
        $this->assertInstanceOf(ProvisioningCapableInterface::class, $driver);

        // Callers check capabilities with instanceof, so an undeclared one keeps the legacy path.
        // Disk, network card, export, backup and configuration-ISO are not built for the agent yet.
        $this->assertNotInstanceOf(\NextDeveloper\IAAS\Contracts\DiskCapableInterface::class, $driver);
        $this->assertNotInstanceOf(\NextDeveloper\IAAS\Contracts\NetworkCapableInterface::class, $driver);
        $this->assertNotInstanceOf(\NextDeveloper\IAAS\Contracts\BackupCapableInterface::class, $driver);
        $this->assertNotInstanceOf(\NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface::class, $driver);
    }

    public function test_power_operations_map_to_the_right_agent_operations()
    {
        Http::fake(['*' => Http::response($this->ok())]);

        $driver = $this->driver();
        $vm     = $this->vm();

        $driver->start($vm);
        $driver->stop($vm);
        $driver->stop($vm, true);
        $driver->restart($vm);
        $driver->restart($vm, true);
        $driver->pause($vm);
        $driver->suspend($vm);

        $payloads = $this->sentOperations();

        $this->assertSame([
            'xenserver.vm.start',
            'xenserver.vm.shutdown',
            'xenserver.vm.force_shutdown',
            'xenserver.vm.reboot',
            'xenserver.vm.force_reboot',
            'xenserver.vm.pause',
            'xenserver.vm.suspend',
        ], array_column($payloads, 'operation'));

        foreach ($payloads as $payload) {
            $this->assertSame('xen-vm-1', $payload['params']['uuid']);
        }
    }

    public function test_delete_sends_confirm_true()
    {
        Http::fake(['*' => Http::response($this->ok())]);

        $this->assertTrue($this->driver()->delete($this->vm()));

        $payload = $this->sentOperations()[0];
        $this->assertSame('xenserver.vm.destroy', $payload['operation']);
        $this->assertTrue($payload['params']['confirm']);
    }

    public function test_resume_uses_unpause_for_paused_vm_and_resume_for_suspended_vm()
    {
        Http::fake(['*' => Http::sequence()
            ->push($this->ok(['params' => ['power-state' => 'paused']]))
            ->push($this->ok())
            ->push($this->ok(['params' => ['power-state' => 'suspended']]))
            ->push($this->ok())]);

        $driver = $this->driver();
        $driver->resume($this->vm());
        $driver->resume($this->vm());

        $this->assertSame(
            ['xenserver.vm.param_list', 'xenserver.vm.unpause', 'xenserver.vm.param_list', 'xenserver.vm.resume'],
            array_column($this->sentOperations(), 'operation')
        );
    }

    public function test_hypervisor_data_is_mapped_to_the_xe_style_flat_shape()
    {
        Http::fake(['*' => Http::response($this->ok([
            'params' => ['uuid' => 'xen-vm-1', 'power-state' => 'running', 'is-a-snapshot' => false, 'VCPUs-max' => 4],
            'maps'   => ['xenstore-data' => ['vm-data/hostname' => 'web1']],
            'sets'   => ['allowed-operations' => ['clean_shutdown', 'pause']],
        ]))]);

        $data = $this->driver()->getHypervisorData($this->vm());

        $this->assertSame('running', $data['power-state']);
        $this->assertSame('false', $data['is-a-snapshot']);
        $this->assertSame('4', $data['VCPUs-max']);
        $this->assertSame(['vm-data/hostname' => 'web1'], $data['xenstore-data']);
        $this->assertSame(['clean_shutdown', 'pause'], $data['allowed-operations']);
    }

    public function test_mapper_keeps_an_already_flat_reply_usable()
    {
        $this->assertSame(
            ['power-state' => 'halted', 'hvm' => 'true'],
            AgentResultMapper::paramList(['power-state' => 'halted', 'hvm' => true])
        );
    }

    public function test_resize_sends_cpu_and_equal_memory_limits_in_bytes()
    {
        Http::fake(['*' => Http::response($this->ok())]);

        try {
            $this->driver()->resize($this->vm(), 4, 2048, 2);
        } catch (\TypeError $e) {
            // The unsaved test VM has no DB row, so $vm->fresh() returns null at the very end.
            // Everything under test (the two agent calls) has already been sent by then.
        }

        $payloads = $this->sentOperations();

        $this->assertSame('xenserver.vm.param_set', $payloads[0]['operation']);
        $this->assertSame('4', $payloads[0]['params']['set']['VCPUs-max']);
        $this->assertSame('4', $payloads[0]['params']['set']['VCPUs-at-startup']);

        $bytes = 2048 * 1024 * 1024;
        $this->assertSame('xenserver.vm.memory_limits_set', $payloads[1]['operation']);
        foreach (['static_min', 'static_max', 'dynamic_min', 'dynamic_max'] as $limit) {
            $this->assertSame($bytes, $payloads[1]['params'][$limit]);
        }
    }

    public function test_agent_failure_surfaces_instead_of_falling_back_to_ssh()
    {
        Http::fake(['*' => Http::response($this->ok(['success' => false, 'message' => 'vm is running']))]);

        $this->expectException(\NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException::class);
        $this->expectExceptionMessage('vm is running');

        $this->driver()->start($this->vm());
    }
}
