<?php

namespace NextDeveloper\IAAS\Tests\Integration;

use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Contracts\HostSyncInterface;
use NextDeveloper\IAAS\Contracts\ProvisioningCapableInterface;
use NextDeveloper\IAAS\Contracts\ResizeCapableInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\ProvisioningAlgorithms\ComputeMembers\UtilizeComputeMembers;
use NextDeveloper\IAAS\ProvisioningAlgorithms\StorageVolumes\UtilizeStorageVolumes;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\VirtualDiskImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

/**
 * REAL integration test of the customer "create VM" flow on a host managed through the HTTPS agent.
 * It writes to the dev database and can import a VM onto the host, so it never runs unless these
 * are set:
 *
 *   XEN_AGENT_TEST_POOL=<compute pool uuid>       pool the VM is created in
 *   XEN_AGENT_TEST_IMAGE=<repository image uuid>  image the VM is imported from
 *   XEN_AGENT_TEST_MEMBER=<compute member uuid>   used to restore used_ram afterwards
 *   SSH_DRY_RUN=false
 *
 * Two tests:
 *  - test_commit_steps_one_by_one runs the same stages as the Commit action
 *    (Actions/VirtualMachines/Commit.php), each one a separate, labelled step that calls the same
 *    driver method Commit calls. It stops at the first stage the agent driver cannot do yet, with
 *    a message naming it, so it doubles as the to-do list for provisioning.
 *  - test_commit_action runs the real Commit action as a black box (the control panel's deploy).
 *
 * The cloud-init config ISO is not part of the agent's job: Commit builds it through the
 * configuration-ISO capability, which falls back to the repository server over SSH.
 *
 * The created VM is left in place so the result can be inspected, delete it afterwards.
 */
class XenAgentVmImportTest extends TestCase
{
    private ?ComputeMembers $member = null;
    private $usedRamBefore = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['XEN_AGENT_TEST_POOL', 'XEN_AGENT_TEST_IMAGE', 'XEN_AGENT_TEST_MEMBER'] as $name) {
            if (!env($name)) {
                $this->markTestSkipped('Set ' . $name . ' to run the live VM import test.');
            }
        }

        config(['leo.debug.ssh.dry_run' => false]);

        UserHelper::setAdminAsCurrentUser();

        $this->member = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', env('XEN_AGENT_TEST_MEMBER'))
            ->firstOrFail();

        // Commit adds the VM's RAM to the member's used_ram with saveQuietly() before it knows
        // whether the import works, so a failed run leaves it inflated. Put it back afterwards.
        $this->usedRamBefore = $this->member->used_ram;
    }

    protected function tearDown(): void
    {
        if ($this->member && $this->usedRamBefore !== null) {
            $this->member->fresh()?->updateQuietly(['used_ram' => $this->usedRamBefore]);
        }

        parent::tearDown();
    }

    // -- the staged clone of Commit ---------------------------------------------------------------

    public function test_commit_steps_one_by_one()
    {
        $manager = app(VirtualMachineManager::class);

        // Stage 0 - preconditions (not part of Commit: data the wizard assumes is already set up)
        $this->step('0. preconditions: pool, image, repository, shared storage pool');
        [$pool, $image, $repository, $storagePool] = $this->preconditions();

        // Stage 1 - the wizard: draft VM + root disk + network card
        $this->step('1. wizard: create the draft VM, its disk and its network card');
        $vm = $this->createDraftVm($pool, $image, $storagePool);

        // Commit.php:354-362 - pick the compute member (importVirtualMachine)
        $this->step('2. Commit: find the best compute member in the pool (UtilizeComputeMembers)');
        $computeMember = (new UtilizeComputeMembers($pool))->calculate($vm->ram, $vm->cpu);
        $this->assertNotNull($computeMember, 'No compute member of the pool can take this VM.');
        $this->assertEquals($this->member->id, $computeMember->id, 'The scheduler picked another member than the agent one.');
        $vm->update(['iaas_compute_member_id' => $computeMember->id]);

        // Commit.php:~395-440 - pick the storage volume ("star" pool: SSD/NVMe pool of the cloud node)
        $this->step('3. Commit: find the best storage volume (UtilizeStorageVolumes)');
        $storageVolume = (new UtilizeStorageVolumes($storagePool))->calculate($computeMember, 20);
        $this->assertNotNull($storageVolume, 'No storage volume of the storage pool can take a 20 GB disk.');

        // Commit.php:450-470 - import. The driver must implement ProvisioningCapableInterface.
        $this->step('4. Commit: mount the repository and import the image (importFromImage, not lazy)');
        $driver = $manager->getAdapter($vm);
        $this->assertInstanceOf(
            ProvisioningCapableInterface::class,
            $driver,
            'The agent driver cannot provision yet: ProvisioningCapableInterface is not implemented ' .
            '(mountRepository, importFromImage, getVmParametersByRef, rename, injectGuestMetadata, reconcile*).'
        );

        $driver->importFromImage($vm, $computeMember, $repository, $storageVolume, $image, false);
        $vm = $vm->fresh();
        $this->assertNotEmpty($vm->hypervisor_uuid, 'The import did not give the VM a hypervisor_uuid.');

        // Commit.php:164-181 - after the import
        $this->step('5. Commit: unmount the repository, inject metadata, rename the VM on the host');
        $driver->unmountRepository($computeMember, $repository);
        $driver->injectGuestMetadata($vm, 'api', config('app.url'));
        $driver->renameVirtualMachine($vm);

        // Commit.php postImportConfiguration() - record the VM's parameters
        $this->step('6. Commit: read the VM parameters from the host and mark the VM as no longer a draft');
        $vmParams = $driver->getVmParametersByRef($computeMember, $vm->hypervisor_uuid);
        $this->assertSame($vm->hypervisor_uuid, $vmParams['uuid'] ?? null);
        $vm->update([
            'hypervisor_data' => $vmParams,
            'state'           => $vmParams['power-state'],
            'is_draft'        => false,
            'status'          => 'halted',
        ]);

        // Commit.php:198-205 - host sync
        $this->step('7. Commit: sync the compute member (HostSyncInterface::syncMember)');
        $hostDriver = $manager->getAdapterForComputeMember($computeMember);
        $this->assertInstanceOf(HostSyncInterface::class, $hostDriver);
        $hostDriver->syncMember($computeMember);

        // Commit.php setCpuRam()
        $this->step('8. Commit: set CPU and RAM (ResizeCapableInterface::resize)');
        $this->assertInstanceOf(ResizeCapableInterface::class, $driver);
        $driver->resize($vm, $vm->cpu, $vm->ram);

        // Commit.php setupDisks()
        $this->step('9. Commit: reconcile the disks against the host (reconcileDiskConfiguration)');
        $driver->reconcileDiskConfiguration($vm);

        // Commit.php setupNetworking()
        $this->step('10. Commit: reconcile the network cards against the host (reconcileNetworkConfiguration)');
        $driver->reconcileNetworkConfiguration($vm);

        // Commit.php stage 5
        $this->step('11. Commit: read the real power state from the host');
        $vmParams = $driver->getHypervisorData($vm);
        $this->assertSame('halted', $vmParams['power-state'] ?? null, 'A freshly imported VM should be halted.');
    }

    // -- the real action, as the control panel runs it -------------------------------------------------

    public function test_commit_action()
    {
        [$pool, $image, , $storagePool] = $this->preconditions();

        $vm = $this->createDraftVm($pool, $image, $storagePool);

        // Default lazy deploy, run synchronously: the import itself would be backgrounded.
        (new Commit($vm))->handle();

        $vm = VirtualMachines::withoutGlobalScopes()->where('uuid', $vm->uuid)->first();

        fwrite(STDERR, "After Commit: status={$vm->status} is_draft=" . json_encode((bool) $vm->is_draft) .
            ' hypervisor_uuid=' . json_encode($vm->hypervisor_uuid) .
            ' member=' . json_encode($vm->iaas_compute_member_id) . "\n");

        $this->assertNotEmpty($vm->iaas_compute_member_id, 'No compute member was assigned.');
        $this->assertNotEmpty($vm->hypervisor_uuid, 'The VM was not imported on the host (no hypervisor_uuid).');
    }

    // -- helpers --------------------------------------------------------------------------------

    private function step(string $label): void
    {
        fwrite(STDERR, "\n[step] " . $label . "\n");
    }

    /**
     * What the wizard assumes is already in place. Checked before anything is created, so a
     * missing piece does not leave a draft VM behind.
     *
     * @return array{ComputePools, RepositoryImages, Repositories, StoragePools}
     */
    private function preconditions(): array
    {
        $pool  = ComputePools::withoutGlobalScopes()->where('uuid', env('XEN_AGENT_TEST_POOL'))->firstOrFail();
        $image = RepositoryImages::withoutGlobalScopes()->where('uuid', env('XEN_AGENT_TEST_IMAGE'))->firstOrFail();

        $repository = Repositories::withoutGlobalScopes()->where('id', $image->iaas_repository_id)->firstOrFail();

        // A "star" pool makes the customer pick the shared storage pool (SSD/SAS/NVMe) for the root
        // disk, so a shared volume of this member must belong to a storage pool.
        $sharedVolume = ComputeMemberStorageVolumes::withoutGlobalScopes()
            ->where('iaas_compute_member_id', $this->member->id)
            ->where('is_local_storage', false)
            ->whereNotNull('iaas_storage_volume_id')
            ->first();

        $storagePoolId = $sharedVolume
            ? StorageVolumes::withoutGlobalScopes()->where('id', $sharedVolume->iaas_storage_volume_id)->value('iaas_storage_pool_id')
            : null;

        $storagePool = $storagePoolId
            ? StoragePools::withoutGlobalScopes()->where('id', $storagePoolId)->first()
            : null;

        if (!$storagePool) {
            $this->fail('The shared storage volume of this member belongs to no storage pool, so a customer disk ' .
                'cannot be placed on it in a star pool. Assign the volume (and its storage member) to an ' .
                'SSD/SAS/NVMe storage pool first. The SSH path behaves the same: shared storage is created ' .
                'without a pool and an operator assigns it.');
        }

        return [$pool, $image, $repository, $storagePool];
    }

    /**
     * The three wizard steps: the draft VM (name, RAM in GB, image, pool), one root disk on the
     * chosen storage pool, and one network card.
     */
    private function createDraftVm(ComputePools $pool, RepositoryImages $image, StoragePools $storagePool): VirtualMachines
    {
        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $pool->iaas_cloud_node_id)
            ->where('vlan', -1)
            ->orderBy('id')
            ->firstOrFail();

        $vm = VirtualMachinesService::create([
            'name'                      => 'agent-import-test-' . now()->format('His'),
            'ram'                       => 2,
            'iaas_repository_image_id'  => $image->uuid,
            'iaas_compute_pool_id'      => $pool->uuid,
        ]);

        $this->assertTrue((bool) $vm->is_draft, 'The new VM should be a draft before Commit.');

        VirtualDiskImagesService::create([
            'name'                      => 'Disk of: ' . $vm->name,
            'size'                      => 20,
            'device_number'             => 0,
            'iaas_storage_pool_id'      => $storagePool->uuid,
            'iaas_virtual_machine_id'   => $vm->uuid,
        ]);

        VirtualNetworkCardsService::create([
            'name'                      => 'eth0',
            'device_number'             => 0,
            'iaas_network_id'           => $network->uuid,
            'iaas_virtual_machine_id'   => $vm->uuid,
            'is_draft'                  => true,
        ]);

        fwrite(STDERR, "\nCreated draft VM {$vm->uuid} ({$vm->name})\n");

        return $vm;
    }
}
