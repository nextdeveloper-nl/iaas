<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2;

use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\CommentsService;
use Nextdeveloper\IAAS\Contracts\CloneCapableInterface;
use Nextdeveloper\IAAS\Contracts\SnapshotCapableInterface;
use Nextdeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\AdapterNotFoundException;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

class VirtualMachineManager
{
    private array $adapters = [];

    public function __construct()
    {
        // Adapters will be registered via service provider
    }

    /**
     * Register an adapter for a platform
     */
    public function registerAdapter(string $platform, string $adapterClass): void
    {
        $this->adapters[$platform] = $adapterClass;
    }

    /**
     * Get adapter instance for a platform
     */
    private function getAdapter(VirtualMachines $vm): VirtualMachineAdapterInterface
    {
        $computePool = VirtualMachinesService::getComputePool($vm);

        if (!isset($this->adapters[$computePool->virtualization])) {
            throw new AdapterNotFoundException("No adapter registered for platform: {$computePool->virtualization}");
        }

        $config = config("virtualization.platforms.{$computePool->virtualization}");

        if (!$config) {
            throw new AdapterNotFoundException("No configuration found for platform: {$computePool->virtualization}");
        }

        return app($this->adapters[$computePool->virtualization], [
            'config' => $config,
            'compute_pool' => $computePool,
        ]);
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
            $hypervisor->start($vm);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }

        return $vm->fresh();
    }

    public function stop(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $vm->updateState('halting');

        try {
            $hypervisor = $this->getAdapter($vm);
            $hypervisor->stop($vm, $force);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }

        return $vm->fresh();
    }

    public function restart(VirtualMachines $vm, bool $force = false): VirtualMachines
    {
        $vm->updateState('restarting');

        try {
            $hypervisor = $this->getAdapter($vm);
            $hypervisor->restart($vm, $force);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }

        return $vm->fresh();
    }

    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null) : VirtualMachines
    {
        $vm->updateState('taking-snapshot');

        $hypervisor = $this->getAdapter($vm);

        if (!$hypervisor instanceof SnapshotCapableInterface) {
            throw new \Exception("Platform {$vm->platform} does not support snapshots");
        }

        try {
            $snapshot = $hypervisor->createSnapshot($vm, $name, $description);

            CommentsService::createSystemComment('Created snapshot for {$name}', $vm);

            return $snapshot;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function restoreSnapshot(VirtualMachines $vm, VirtualMachines $snapshot) : VirtualMachines
    {
        $vm->updateState('taking-snapshot');

        $hypervisor = $this->getAdapter($vm);

        if (!$hypervisor instanceof SnapshotCapableInterface) {
            throw new \Exception("Platform {$vm->platform} does not support snapshots");
        }

        try {
            $snapshot = $hypervisor->restoreSnapshot($vm, $snapshot);

            CommentsService::createSystemComment('Created snapshot for {$name}', $vm);

            return $snapshot;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function clone(VirtualMachines $vm, string $name, array $options = []) : VirtualMachines
    {
        $hypervisor = $this->getAdapter($vm);

        if (!$hypervisor instanceof CloneCapableInterface) {
            throw new \Exception("Platform {$vm->platform} does not support cloning");
        }

        try {
            $clone = $hypervisor->clone($vm, $name, $options);

            CommentsService::createSystemComment('Cloned {$name}', $vm);
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function delete(VirtualMachines $vm, bool $force = false): bool
    {
        $hypervisor = $this->getAdapter($vm);

        try {
            $result = $hypervisor->delete($vm);

            if($result) {
                CommentsService::createSystemComment('Deleted {name}', $vm);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            CommentsService::createSystemComment($e->getMessage(), $vm);
            throw $e;
        }
    }

    public function getHypervisorData(VirtualMachines $vm) : array
    {
        $hypervisor = $this->getAdapter($vm);
        $data = $hypervisor->getHypervisorData($vm);

        return $data;
    }
}
