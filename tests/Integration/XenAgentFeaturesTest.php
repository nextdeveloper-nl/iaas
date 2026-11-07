<?php

namespace NextDeveloper\IAAS\Tests\Integration;

use NextDeveloper\IAAS\Actions\VirtualMachines\Delete;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

/**
 * Live tests of the rest of the agent driver on a VM made by XenAgentVmImportTest.
 *
 *   XEN_AGENT_TEST_VM=<virtual machine uuid>
 *
 * Power (force operations), snapshot, clone, console, resize on a running VM and a repeated host
 * sync. Snapshots and clones are removed again, the clone through the real Delete action (which also
 * checks vm.destroy with expected_name). The test VM is left halted.
 */
class XenAgentFeaturesTest extends TestCase
{
    private ?VirtualMachines $vm = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!env('XEN_AGENT_TEST_VM')) {
            $this->markTestSkipped('Set XEN_AGENT_TEST_VM to run the live feature tests.');
        }

        config(['leo.debug.ssh.dry_run' => false]);

        UserHelper::setAdminAsCurrentUser();

        $this->vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', env('XEN_AGENT_TEST_VM'))
            ->firstOrFail();
    }

    protected function tearDown(): void
    {
        // Leave the VM halted whatever happened.
        try {
            if ($this->vm && $this->live() !== 'halted') {
                $this->driver()->stop($this->vm->fresh(), true);
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, "\n[features] could not halt the VM in tearDown: " . $e->getMessage() . "\n");
        }

        parent::tearDown();
    }

    private function driver()
    {
        return app(VirtualMachineManager::class)->getAdapter($this->vm->fresh());
    }

    private function live(?VirtualMachines $vm = null): ?string
    {
        $vm ??= $this->vm;

        return $this->driver()->getHypervisorData($vm->fresh())['power-state'] ?? null;
    }

    private function note(string $line): void
    {
        fwrite(STDERR, "\n[features] {$line}\n");
    }

    private function waitFor(string $state, ?VirtualMachines $vm = null): void
    {
        for ($i = 0; $i < 15 && $this->live($vm) !== $state; $i++) {
            sleep(2);
        }

        $this->assertSame($state, $this->live($vm));
    }

    // -- 2. power: the force operations ------------------------------------------------------------

    public function test_force_power_operations()
    {
        $driver = $this->driver();

        if ($this->live() === 'halted') {
            $driver->start($this->vm->fresh());
            $this->waitFor('running');
        }

        $steps = [
            ['force restart',    fn() => $driver->restart($this->vm->fresh(), true), 'running'],
            ['force stop',       fn() => $driver->stop($this->vm->fresh(), true),    'halted'],
            ['stop when halted', fn() => $driver->stop($this->vm->fresh(), true),    'halted'],
            ['start',            fn() => $driver->start($this->vm->fresh()),         'running'],
            ['force stop',       fn() => $driver->stop($this->vm->fresh(), true),    'halted'],
        ];

        foreach ($steps as [$label, $do, $expected]) {
            $this->note("power: {$label}");
            $do();
            $this->waitFor($expected);
            $this->note("        live power-state={$this->live()} db status=" . $this->vm->fresh()->status);
        }
    }

    // -- 3. snapshots ---------------------------------------------------------------------------------

    public function test_snapshot_operations()
    {
        $driver = $this->driver();

        $this->note('snapshot: create A');
        $a = $driver->createSnapshot($this->vm->fresh(), 'agent-test-snap-a');
        $this->assertTrue((bool) $a->is_snapshot);
        $this->assertNotEmpty($a->hypervisor_uuid);

        try {
            $this->note('snapshot: list');
            $uuids = array_map(fn($s) => $s->uuid, $driver->listSnapshots($this->vm->fresh()));
            $this->assertContains($a->uuid, $uuids);

            $this->note('snapshot: delete A');
            $this->assertTrue($driver->deleteSnapshot($this->vm->fresh(), $a->uuid));
            $a->delete();
            $a = null;
        } finally {
            if ($a) {
                $driver->deleteSnapshot($this->vm->fresh(), $a->uuid);
                $a->delete();
            }
        }

        $this->note('snapshot: create B and restore it (convert the snapshot to a VM)');
        $b = $driver->createSnapshot($this->vm->fresh(), 'agent-test-snap-b');

        try {
            $driver->restoreSnapshot($this->vm->fresh(), $b->uuid);

            $params = $driver->getHypervisorData($b->fresh());
            $this->assertNotEmpty($params['uuid'] ?? null, 'The converted snapshot is not readable on the host.');
            $this->note('        converted: is-a-template=' . ($params['is-a-template'] ?? '?') . ' is-a-snapshot=' . ($params['is-a-snapshot'] ?? '?'));
        } finally {
            $this->removeVm($b->fresh());
        }
    }

    // -- 4. clone -----------------------------------------------------------------------------------------

    public function test_clone()
    {
        $driver = $this->driver();

        $this->note('clone: create');
        $clone = $driver->clone($this->vm->fresh(), 'agent-test-clone-' . now()->format('His'));

        try {
            $this->assertNotEmpty($clone->hypervisor_uuid);
            $this->assertNotSame($this->vm->hypervisor_uuid, $clone->hypervisor_uuid);

            $this->note("clone: on the host as {$clone->hypervisor_uuid}, power-state=" . $this->live($clone));
            $this->assertSame('halted', $this->live($clone));
        } finally {
            $this->removeVm($clone->fresh());
        }
    }

    // -- 5. console -----------------------------------------------------------------------------------------

    public function test_console()
    {
        $driver = $this->driver();

        if ($this->live() !== 'running') {
            $driver->start($this->vm->fresh());
            $this->waitFor('running');
        }

        $this->note('console: read the console session of the running VM');

        try {
            $session = $driver->getConsoleUrl($this->vm->fresh());
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Known gap, not a driver bug: the console URL needs a XenAPI session, which the shared
            // code opens with the member's SSH username/password. An agent-only member has none, and
            // the agent has no operation that returns a console session (console_list returns only
            // the consoles). Needs an agent-side session or proxy operation.
            $this->markTestIncomplete('Console not available for an agent-only member: it has no SSH password for the XenAPI session.');
        }

        $this->note('        protocol=' . $session->protocol . ' url=' . (string) preg_replace('/(ref|session)[^&]*/i', '$1=***', (string) $session->url));
        $this->assertNotEmpty($session->url, 'The console session has no URL.');
    }

    // -- 6. resize ----------------------------------------------------------------------------------------------

    public function test_resize_on_a_running_vm_and_back_on_a_halted_one()
    {
        $driver = $this->driver();

        $cpu = (int) $this->vm->cpu;
        $ram = (int) $this->vm->ram;

        if ($this->live() !== 'running') {
            $driver->start($this->vm->fresh());
            $this->waitFor('running');
        }

        $this->note('resize: running VM, cpu ' . $cpu . ' -> ' . ($cpu + 1));
        try {
            $driver->resize($this->vm->fresh(), $cpu + 1, $ram);
            $this->note('        accepted on a running VM');
        } catch (\Throwable $e) {
            $this->note('        refused on a running VM: ' . substr($e->getMessage(), 0, 300));
            $this->assertStringContainsString('xenserver.', $e->getMessage(), 'The refusal should come from the agent, not from a crash.');
        }

        $driver->stop($this->vm->fresh(), true);
        $this->waitFor('halted');

        $this->note('resize: halted VM, cpu ' . $cpu . ' -> ' . ($cpu + 1) . ' and back');
        $driver->resize($this->vm->fresh(), $cpu + 1, $ram);
        $this->assertSame($cpu + 1, (int) ($driver->getHypervisorData($this->vm->fresh())['VCPUs-max'] ?? 0));

        $driver->resize($this->vm->fresh(), $cpu, $ram);
        $this->assertSame($cpu, (int) ($driver->getHypervisorData($this->vm->fresh())['VCPUs-max'] ?? 0));
    }

    // -- 7. a repeated host sync ----------------------------------------------------------------------------------

    public function test_repeated_host_sync_does_not_duplicate_rows()
    {
        $driver = $this->driver();
        $member = VirtualMachinesService::getComputeMember($this->vm->fresh());

        $count = fn() => [
            'vms'   => VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $member->id)->count(),
            'disks' => VirtualDiskImages::withoutGlobalScopes()->whereIn('iaas_virtual_machine_id',
                VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $member->id)->pluck('id'))->count(),
            'nics'  => VirtualNetworkCards::withoutGlobalScopes()->whereIn('iaas_virtual_machine_id',
                VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $member->id)->pluck('id'))->count(),
        ];

        $this->note('sync: first run');
        $driver->syncVirtualMachines($member);
        $first = $count();

        $this->note('sync: second run');
        $driver->syncVirtualMachines($member->fresh());
        $second = $count();

        $this->note('        rows after run 1: ' . json_encode($first) . ', after run 2: ' . json_encode($second));
        $this->assertSame($first, $second, 'A second sync changed the number of rows.');
    }

    // -- helpers --------------------------------------------------------------------------------------------------------

    /**
     * Removes a VM the test made (clone, converted snapshot) the way the product does it: sync so its
     * disks have rows, then the real Delete action, which also destroys the disks on the host.
     */
    private function removeVm(VirtualMachines $vm): void
    {
        $this->note("cleanup: delete {$vm->name} through the Delete action");

        $member = VirtualMachinesService::getComputeMember($vm);
        $this->driver()->syncVirtualMachines($member);

        (new Delete($vm->fresh()))->handle();

        $this->assertNull(
            VirtualMachines::where('uuid', $vm->uuid)->first(),
            'The VM row is still there after Delete.'
        );
    }
}
