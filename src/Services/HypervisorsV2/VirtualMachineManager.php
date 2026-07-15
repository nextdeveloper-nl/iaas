<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2;

use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Contracts\CloneCapableInterface;
use NextDeveloper\IAAS\Contracts\SnapshotCapableInterface;
use NextDeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\AdapterNotFoundException;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * Resolves and dispatches to the hypervisor driver registered for a given
 * ComputePools.virtualization value (see config/virtualization.php). This is the single
 * place Actions/Jobs/Controllers go through instead of instantiating a concrete
 * *XenService/driver class directly - see docs/hypervisor-driver-architecture.md.
 */
class VirtualMachineManager
{
    private array $adapters = [];

    /**
     * Register the driver class for a virtualization string. Called once per configured
     * platform from IAASServiceProvider, driven by config/virtualization.php.
     */
    public function registerAdapter(string $platform, string $adapterClass): void
    {
        $this->adapters[$platform] = $adapterClass;
    }

    /**
     * Resolves the driver instance registered for a given virtualization string.
     * This is the core resolution primitive - VM/host/disk/network level callers
     * all end up here, they just differ in how they arrive at the $virtualization value.
     */
    public function resolveDriver(string $virtualization): VirtualMachineAdapterInterface
    {
        if (!isset($this->adapters[$virtualization])) {
            throw new AdapterNotFoundException("No driver registered for platform: {$virtualization}");
        }

        $config = config("virtualization.platforms.{$virtualization}");

        if (!$config) {
            throw new AdapterNotFoundException("No configuration found for platform: {$virtualization}");
        }

        return app($this->adapters[$virtualization], [
            'config' => $config,
        ]);
    }

    /**
     * Resolves the driver for a VM via its compute pool's virtualization string.
     */
    public function getAdapter(VirtualMachines $vm): VirtualMachineAdapterInterface
    {
        $computePool = VirtualMachinesService::getComputePool($vm);

        return $this->resolveDriver($computePool->virtualization);
    }

    /**
     * Resolves the driver for a compute member (host-level operations - see
     * HostSyncInterface) via its compute pool's virtualization string.
     */
    public function getAdapterForComputeMember(ComputeMembers $computeMember): VirtualMachineAdapterInterface
    {
        return $this->resolveDriver($computeMember->computePools->virtualization);
    }

    /**
     * Resolves the driver directly from a compute pool - used wherever the caller
     * already has the pool and doesn't need to look it up via a VM/compute member.
     */
    public function getAdapterForComputePool(ComputePools $computePool): VirtualMachineAdapterInterface
    {
        return $this->resolveDriver($computePool->virtualization);
    }

    public function sync(VirtualMachines $vm) : VirtualMachines
    {
        $hypervisor = $this->getAdapter($vm);

        return $hypervisor->sync($vm);
    }

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $vm->updateState('starting');

        try {
            $hypervisor = $this->getAdapter($vm);

            return $hypervisor->start($vm);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $vm->updateState('halting');

        try {
            $hypervisor = $this->getAdapter($vm);

            return $hypervisor->stop($vm, $force);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $vm->updateState('restarting');

        try {
            $hypervisor = $this->getAdapter($vm);

            return $hypervisor->restart($vm, $force);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function pause(VirtualMachines $vm): VirtualMachines
    {
        $vm->updateState('pausing');

        try {
            $hypervisor = $this->getAdapter($vm);

            return $hypervisor->pause($vm);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function resume(VirtualMachines $vm): VirtualMachines
    {
        $vm->updateState('resuming');

        try {
            $hypervisor = $this->getAdapter($vm);

            return $hypervisor->resume($vm);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null) : VirtualMachines
    {
        $vm->updateState('taking-snapshot');

        $hypervisor = $this->getAdapter($vm);

        if (!$hypervisor instanceof SnapshotCapableInterface) {
            throw new \Exception("Platform {$this->platformOf($vm)} does not support snapshots");
        }

        try {
            $snapshot = $hypervisor->createSnapshot($vm, $name, $description);

            CommentsService::createSystemComment('Created snapshot ' . $name, $vm);

            return $snapshot;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function restoreSnapshot(VirtualMachines $vm, string $snapshotId) : bool
    {
        $hypervisor = $this->getAdapter($vm);

        if (!$hypervisor instanceof SnapshotCapableInterface) {
            throw new \Exception("Platform {$this->platformOf($vm)} does not support snapshots");
        }

        try {
            $result = $hypervisor->restoreSnapshot($vm, $snapshotId);

            CommentsService::createSystemComment('Restored snapshot ' . $snapshotId, $vm);

            return $result;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function clone(VirtualMachines $vm, string $newName, array $options = []) : VirtualMachines
    {
        $hypervisor = $this->getAdapter($vm);

        if (!$hypervisor instanceof CloneCapableInterface) {
            throw new \Exception("Platform {$this->platformOf($vm)} does not support cloning");
        }

        try {
            $clone = $hypervisor->clone($vm, $newName, $options);

            CommentsService::createSystemComment('Cloned to ' . $newName, $vm);

            return $clone;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function delete(VirtualMachines $vm): bool
    {
        try {
            $hypervisor = $this->getAdapter($vm);
            $result = $hypervisor->delete($vm);

            if ($result) {
                CommentsService::createSystemComment('Deleted ' . $vm->name, $vm);
            }

            return $result;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function getHypervisorData(VirtualMachines $vm) : array
    {
        return $this->getAdapter($vm)->getHypervisorData($vm);
    }

    private function platformOf(VirtualMachines $vm) : string
    {
        return VirtualMachinesService::getComputePool($vm)->virtualization;
    }
}
