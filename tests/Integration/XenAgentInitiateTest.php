<?php

namespace NextDeveloper\IAAS\Tests\Integration;

use NextDeveloper\IAAS\Actions\ComputeMembers\Initiate;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\Agent\XenAgentClient;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\XenServer82AgentDriver;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

/**
 * REAL integration test: talks to a live xenserver agent and uses the real (dev) database.
 * It never runs in a normal test run - set the member to use:
 *
 *   XEN_AGENT_TEST_MEMBER=<compute member uuid> SSH_DRY_RUN=false \
 *     php vendor/bin/phpunit vendor/nextdeveloper/iaas/tests/Integration/XenAgentInitiateTest.php
 *
 * The member must already exist (php artisan iaas:add-agent-compute-member) and sit in a pool
 * on an "-agent" platform. Everything the test asks the host is read-only.
 */
class XenAgentInitiateTest extends TestCase
{
    private ComputeMembers $member;

    protected function setUp(): void
    {
        parent::setUp();

        $uuid = env('XEN_AGENT_TEST_MEMBER');

        if (!$uuid) {
            $this->markTestSkipped('Set XEN_AGENT_TEST_MEMBER=<compute member uuid> to run against a live agent.');
        }

        // The dev .env has SSH_DRY_RUN=true, which stops the client from sending anything.
        config(['leo.debug.ssh.dry_run' => false]);

        UserHelper::setAdminAsCurrentUser();

        $this->member = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    public function test_pool_of_the_member_resolves_to_the_agent_driver()
    {
        $driver = app(VirtualMachineManager::class)->getAdapterForComputeMember($this->member);

        $this->assertInstanceOf(
            XenServer82AgentDriver::class,
            $driver,
            'The pool of this member must be on an "-agent" platform to use the agent driver.'
        );
    }

    public function test_agent_is_reachable_with_the_stored_token_and_pin()
    {
        $hosts = (new XenAgentClient($this->member))->call('xenserver.host.list');

        $this->assertNotEmpty($hosts, 'The agent returned no hosts.');
        $this->assertArrayHasKey('uuid', $hosts[0]);
    }

    public function test_agent_driver_can_sync_a_host_so_initiate_has_something_to_run()
    {
        $driver = app(VirtualMachineManager::class)->getAdapterForComputeMember($this->member);

        // Initiate only syncs when the driver is a HostSyncInterface; otherwise it just fires
        // the "initiated" event, so the member would look initiated without anything synced.
        $this->assertInstanceOf(
            HostSyncInterface::class,
            $driver,
            'Initiate does nothing for this driver: it does not implement HostSyncInterface yet.'
        );
    }

    public function test_initiate_action_runs_against_the_agent_member()
    {
        (new Initiate($this->member))->handle();

        $member = $this->member->fresh();

        $this->assertNotEmpty($member->hypervisor_uuid, 'Initiate did not sync the host (no hypervisor_uuid).');
    }

    /**
     * Needs the host sync (Initiate) to have run first: VM disks need the storage volume rows and
     * VM network cards need the interface and network rows.
     */
    public function test_vm_sync_creates_the_vm_disk_and_network_card_records_of_the_host()
    {
        $driver = app(VirtualMachineManager::class)->getAdapterForComputeMember($this->member);

        $driver->syncVirtualMachines($this->member);

        $vms = (new XenAgentClient($this->member))->call('xenserver.vm.list');

        // The test host's VMs can be removed at any time, and there is nothing to compare then.
        if (empty($vms)) {
            $this->markTestSkipped('The host reports no guest VMs, so there is nothing to sync and compare.');
        }

        foreach ($vms as $vm) {
            $dbVm = VirtualMachines::withoutGlobalScopes()->where('hypervisor_uuid', $vm['uuid'])->first();

            $this->assertNotNull($dbVm, 'No VirtualMachines row for ' . $vm['name_label']);
            $this->assertSame($vm['power_state'], $dbVm->status);
            $this->assertEquals($this->member->id, $dbVm->iaas_compute_member_id);

            $this->assertGreaterThan(
                0,
                VirtualDiskImages::withoutGlobalScopes()->where('iaas_virtual_machine_id', $dbVm->id)->count(),
                'No disk or CD record for ' . $vm['name_label']
            );

            // One card record per VIF the agent reports for the VM.
            $this->assertSame(
                count((new XenAgentClient($this->member))->call('xenserver.vif.list', ['uuid' => $vm['uuid']])),
                VirtualNetworkCards::withoutGlobalScopes()->where('iaas_virtual_machine_id', $dbVm->id)->count(),
                'Network card records of ' . $vm['name_label'] . ' do not match its VIFs on the host.'
            );
        }

        // Syncing twice must not duplicate anything.
        $before = [
            VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $this->member->id)->count(),
            VirtualDiskImages::withoutGlobalScopes()->whereIn('iaas_virtual_machine_id',
                VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $this->member->id)->pluck('id'))->count(),
            VirtualNetworkCards::withoutGlobalScopes()->whereIn('iaas_virtual_machine_id',
                VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $this->member->id)->pluck('id'))->count(),
        ];

        $driver->syncVirtualMachines($this->member);

        $after = [
            VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $this->member->id)->count(),
            VirtualDiskImages::withoutGlobalScopes()->whereIn('iaas_virtual_machine_id',
                VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $this->member->id)->pluck('id'))->count(),
            VirtualNetworkCards::withoutGlobalScopes()->whereIn('iaas_virtual_machine_id',
                VirtualMachines::withoutGlobalScopes()->where('iaas_compute_member_id', $this->member->id)->pluck('id'))->count(),
        ];

        $this->assertSame($before, $after, 'A second VM sync changed the record counts (VMs, disks, cards).');
    }

    /**
     * Uses the host's local (lvm) SR, which only holds this host's own VM disks. The shared NFS SR
     * is not used on purpose: it can hold disks of other hosts.
     */
    public function test_volume_disk_sync_updates_the_local_storage_volume()
    {
        $driver = app(VirtualMachineManager::class)->getAdapterForComputeMember($this->member);

        $lvm = collect((new XenAgentClient($this->member))->call('xenserver.sr.list'))->firstWhere('type', 'lvm');
        $this->assertNotNull($lvm, 'The host has no local (lvm) SR.');

        $volume = StorageVolumes::withoutGlobalScopes()->where('hypervisor_uuid', $lvm['uuid'])->firstOrFail();

        $volume = $driver->syncStorageVolumeDisks($this->member, $volume);

        $this->assertGreaterThan(0, $volume->total_hdd, 'The volume size was not refreshed.');
    }
}
