<?php

namespace NextDeveloper\IAAS\Tests\Integration;

use NextDeveloper\IAAS\Actions\VirtualDiskImages\Attach as DiskAttach;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Create as DiskCreate;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Destroy as DiskDestroy;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Detach as DiskDetach;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Resize as DiskResize;
use NextDeveloper\IAAS\Actions\VirtualDiskImages\Sync as DiskSync;
use NextDeveloper\IAAS\Actions\VirtualNetworkCards\Detach as VifDetach;
use NextDeveloper\IAAS\Contracts\DiskCapableInterface;
use NextDeveloper\IAAS\Contracts\NetworkCapableInterface;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\ComputeMemberAgentSyncService;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

/**
 * Live disk and network card (VIF) tests of the agent driver, through the real Actions the control
 * panel runs (Attach, Detach, Create, Resize, Sync, Destroy), on a VM made by XenAgentVmImportTest.
 *
 *   XEN_AGENT_TEST_VM=<virtual machine uuid>   the VM; it is put in the power state each test needs
 *
 * Each test removes what it created, also when it fails half way. The VM itself is left alone.
 */
class XenAgentDevicesTest extends TestCase
{
    private ?VirtualMachines $vm = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!env('XEN_AGENT_TEST_VM')) {
            $this->markTestSkipped('Set XEN_AGENT_TEST_VM to run the live device tests.');
        }

        config(['leo.debug.ssh.dry_run' => false]);

        UserHelper::setAdminAsCurrentUser();

        $this->vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', env('XEN_AGENT_TEST_VM'))
            ->firstOrFail();
    }

    private function driver()
    {
        return app(VirtualMachineManager::class)->getAdapter($this->vm->fresh());
    }

    private function note(string $line): void
    {
        fwrite(STDERR, "\n[devices] {$line}\n");
    }

    private function client()
    {
        return ComputeMemberAgentSyncService::client(VirtualMachinesService::getComputeMember($this->vm->fresh()));
    }

    /** Puts the VM in the wanted power state and syncs the DB status (the disk code reads it). */
    private function ensurePower(string $wanted): void
    {
        $driver = $this->driver();
        $live   = fn() => $driver->getHypervisorData($this->vm->fresh())['power-state'] ?? null;

        if ($live() === $wanted) {
            $driver->sync($this->vm->fresh());
            return;
        }

        if ($wanted === 'running') {
            if ($live() === 'paused') {
                $driver->resume($this->vm->fresh());
            } else {
                $driver->start($this->vm->fresh());
            }
        } else {
            $driver->stop($this->vm->fresh(), true);
        }

        for ($i = 0; $i < 10 && $live() !== $wanted; $i++) {
            sleep(2);
        }

        $this->assertSame($wanted, $live(), "Could not put the VM in '{$wanted}'.");
        $driver->sync($this->vm->fresh());
    }

    // -- disks ------------------------------------------------------------------------------------

    public function test_disk_actions_on_a_halted_vm()
    {
        $this->ensurePower('halted');
        $this->runDiskActions('halted');
    }

    public function test_disk_actions_on_a_running_vm()
    {
        $this->ensurePower('running');
        $this->runDiskActions('running');
    }

    private function runDiskActions(string $power): void
    {
        $this->assertInstanceOf(DiskCapableInterface::class, $this->driver());

        $root = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $this->vm->id)
            ->orderBy('id')
            ->firstOrFail();

        $this->note("disk: create the row through the service ({$power} VM)");
        // For a live VM the service marks it pending update and queues Commit, which builds the
        // cloud-init ISO over SSH on the repository server (not reachable from here, and not what
        // this test is about). Queue to the null driver so only the disk Actions below run.
        $queueDefault = config('queue.default');
        config(['queue.default' => 'null']);

        $vdi = VirtualDiskImagesService::create([
            'name'                    => 'agent-test-disk-' . now()->format('His'),
            'size'                    => 2,
            'iaas_storage_pool_id'    => \NextDeveloper\IAAS\Database\Models\StoragePools::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $root->iaas_storage_pool_id)->firstOrFail()->uuid,
            'iaas_virtual_machine_id' => $this->vm->uuid,
        ]);
        config(['queue.default' => $queueDefault]);
        $this->vm->fresh()->updateQuietly(['is_pending_update' => false]);
        $this->assertNotNull($vdi, 'The service did not create the disk row.');

        // The service numbers the disk by counting the VM's disks through the signed-in user's scope,
        // which does not see the root disk of another account (here: device 0 again). A customer
        // sees their own disks, so set the number the customer would get.
        $vdi->updateQuietly([
            'device_number' => VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $this->vm->id)->where('id', '!=', $vdi->id)->count(),
        ]);

        try {
            $vdi = $vdi->fresh();

            if (!$vdi->hypervisor_uuid) {
                $this->note('disk: Create action');
                (new DiskCreate($vdi))->handle();
                $vdi = $vdi->fresh();
            }
            $this->assertNotEmpty($vdi->hypervisor_uuid, 'Create did not give the disk a hypervisor_uuid.');
            $this->note("disk created: vdi={$vdi->hypervisor_uuid} device={$vdi->device_number}");

            $this->note('disk: Attach action');
            (new DiskAttach($vdi))->handle();
            $vdi = $vdi->fresh();
            $this->assertNotEmpty($vdi->vbd_hypervisor_uuid, 'Attach did not store the VBD uuid.');

            $vbd = ComputeMemberAgentSyncService::flatten(
                $this->client()->call('xenserver.vbd.param_list', ['vbd_uuid' => $vdi->vbd_hypervisor_uuid])
            );
            $this->note('disk attached: vbd currently-attached=' . ($vbd['currently-attached'] ?? '?'));
            if ($power === 'running') {
                $this->assertSame('true', $vbd['currently-attached'] ?? null, 'The disk is not plugged into the running VM.');
            }

            $this->note('disk: Resize action 2 GB -> 3 GB');
            $vdi->updateQuietly(['size' => 3 * 1024 * 1024 * 1024]);

            try {
                (new DiskResize($vdi->fresh()))->handle();

                $vdiParams = ComputeMemberAgentSyncService::flatten(
                    $this->client()->call('xenserver.vdi.param_list', ['vdi_uuid' => $vdi->hypervisor_uuid])
                );
                $this->assertSame((string) (3 * 1024 * 1024 * 1024), (string) $vdiParams['virtual-size'], 'The disk was not grown on the host.');
            } catch (\NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentException $e) {
                // An NFS SR cannot grow a disk that is plugged into a running VM. That is the storage
                // (the agent says so clearly), not a driver fault; on a halted VM resize must work.
                if ($power !== 'running' || !str_contains($e->getMessage(), 'does not support online resize')) {
                    throw $e;
                }
                $this->note('disk: online resize refused by the storage, as expected on a running VM');
                $vdi->updateQuietly(['size' => 2 * 1024 * 1024 * 1024]);
            }

            $this->note('disk: Sync action');
            (new DiskSync($vdi->fresh()))->handle();

            $this->note('disk: Detach action');
            (new DiskDetach($vdi->fresh()))->handle();
            $vdi = $vdi->fresh();
            $this->assertEmpty($vdi->vbd_hypervisor_uuid, 'Detach did not clear the VBD uuid.');

            $this->note('disk: Attach again, then Detach again');
            (new DiskAttach($vdi))->handle();
            $this->assertNotEmpty($vdi->fresh()->vbd_hypervisor_uuid);
            (new DiskDetach($vdi->fresh()))->handle();

            $this->note('disk: Detach when already detached (must not fail)');
            (new DiskDetach($vdi->fresh()))->handle();
        } finally {
            $this->note('disk: Destroy action + remove the row');
            $vdi = $vdi->fresh();
            if ($vdi) {
                if ($vdi->vbd_hypervisor_uuid) {
                    (new DiskDetach($vdi))->handle();
                }
                (new DiskDestroy($vdi->fresh()))->handle();
                $this->assertEmpty($vdi->fresh()->hypervisor_uuid, 'Destroy did not clear the hypervisor_uuid.');
                $vdi->fresh()->delete();
            }
        }
    }

    // -- network cards ----------------------------------------------------------------------------

    public function test_vif_actions_on_a_halted_vm()
    {
        $this->ensurePower('halted');
        $this->runVifActions('halted');
    }

    public function test_vif_actions_on_a_running_vm()
    {
        $this->ensurePower('running');
        $this->runVifActions('running');
    }

    private function runVifActions(string $power): void
    {
        $driver = $this->driver();
        $this->assertInstanceOf(NetworkCapableInterface::class, $driver);

        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $this->vm->id)->orderBy('id')->firstOrFail()->iaas_network_id)
            ->firstOrFail();

        $device = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $this->vm->id)->count();

        $this->note("vif: create a draft card on device {$device} through the service (runs the Attach action, {$power} VM)");
        $vif = VirtualNetworkCardsService::create([
            'name'                    => 'eth' . $device,
            'device_number'           => $device,
            'iaas_network_id'         => $network->uuid,
            'iaas_virtual_machine_id' => $this->vm->uuid,
            'is_draft'                => true,
        ]);
        $this->assertNotNull($vif, 'The service did not create the card.');

        try {
            $vif = $vif->fresh();
            $this->assertNotEmpty($vif->hypervisor_uuid, 'Attach did not give the card a hypervisor_uuid.');
            $this->assertNotEmpty($vif->mac_addr, 'The card has no MAC.');
            $this->note("vif attached: uuid={$vif->hypervisor_uuid} mac={$vif->mac_addr} status={$vif->status} currently-attached=" .
                ($vif->hypervisor_data['currently-attached'] ?? '?'));

            $this->note('vif: sync the card from the host');
            $vif = $driver->syncNetworkCard($vif);
            $this->assertSame($vif->hypervisor_uuid, $vif->hypervisor_data['uuid'] ?? null);

            $this->note('vif: locking mode = locked');
            $this->assertTrue($driver->setIpFilterMode($vif, 'locked'));
            $vif = $driver->syncNetworkCard($vif->fresh());
            $this->assertSame('locked', $vif->hypervisor_data['locking-mode'] ?? null);

            $this->note('vif: apply the IP allow-list of the card (empty list)');
            $this->assertTrue($driver->applyIpFilter($vif));

            $this->note('vif: locking mode = unlocked');
            $this->assertTrue($driver->setIpFilterMode($vif->fresh(), 'unlocked'));
            $vif = $driver->syncNetworkCard($vif->fresh());
            $this->assertSame('unlocked', $vif->hypervisor_data['locking-mode'] ?? null);

            $this->note('vif: locking mode = network_default');
            $this->assertTrue($driver->setIpFilterMode($vif->fresh(), 'network_default'));
            $vif = $driver->syncNetworkCard($vif->fresh());
            $this->assertSame('network_default', $vif->hypervisor_data['locking-mode'] ?? null);

            $this->note('vif: Detach action');
            (new VifDetach($vif->fresh()))->handle();
            $vif = $vif->fresh();
            $this->assertEmpty($vif->hypervisor_uuid, 'Detach did not clear the hypervisor_uuid.');
            $this->assertSame('detached', $vif->status);

            $this->note('vif: Detach again (must not fail)');
            (new VifDetach($vif->fresh()))->handle();
        } finally {
            $this->note('vif: remove the card row');
            $vif = $vif->fresh();
            if ($vif) {
                if ($vif->hypervisor_uuid) {
                    (new VifDetach($vif))->handle();
                }
                $vif->fresh()->delete();
            }
        }
    }
}
