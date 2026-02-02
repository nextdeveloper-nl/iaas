<?php

namespace NextDeveloper\IAAS\Services\HypervisorsV2\XenServer_8_2;

use Illuminate\Support\Facades\Log;
use Nextdeveloper\IAAS\Contracts\CloneCapableInterface;
use Nextdeveloper\IAAS\Contracts\SnapshotCapableInterface;
use Nextdeveloper\IAAS\Contracts\VirtualMachineAdapterInterface;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class XenVmManager implements VirtualMachineAdapterInterface, SnapshotCapableInterface, CloneCapableInterface
{
    public function clone(VirtualMachines $vm, string $newName, array $options = []): VirtualMachines
    {
        // TODO: Implement clone() method.
    }

    public function createSnapshot(VirtualMachines $vm, string $name, ?string $description = null): VirtualMachines
    {
        // TODO: Implement createSnapshot() method.
    }

    public function deleteSnapshot(VirtualMachines $vm, string $snapshotId): bool
    {
        // TODO: Implement deleteSnapshot() method.
    }

    public function restoreSnapshot(VirtualMachines $vm, string $snapshotId): bool
    {
        // TODO: Implement restoreSnapshot() method.
    }

    public function listSnapshots(VirtualMachines $vm): array
    {
        // TODO: Implement listSnapshots() method.
    }

    public function start(VirtualMachines $vm): VirtualMachines
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@start] I am starting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-start uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);

        return self::sync($vm);
    }

    public function stop(VirtualMachines $vm, bool $force = false): bool
    {
        // TODO: Implement stop() method.
    }

    public function restart(VirtualMachines $vm, bool $force = false): bool
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();

        if (config('leo.debug.iaas.compute_members'))
            Log::error('[VirtualMachinesXenService@restart] I am restarting the' .
                ' VM (' . $vm->name . '/' . $vm->uuid . ') from the compute' .
                ' member (' . $computeMember->name . '/' . $computeMember->uuid . ')');

        $command = 'xe vm-reboot uuid=' . $vm->hypervisor_uuid;
        $result = self::performCommand($command, $computeMember);
        $result = $result['output'];

        return true;
    }

    public function pause(VirtualMachines $vm): bool
    {
        // TODO: Implement pause() method.
    }

    public function resume(VirtualMachines $vm): bool
    {
        // TODO: Implement resume() method.
    }

    public function suspend(VirtualMachines $vm): bool
    {
        // TODO: Implement suspend() method.
    }

    public function getHypervisorData(VirtualMachines $vm): array
    {
        // TODO: Implement getHypervisorData() method.
    }

    public function delete(VirtualMachines $vm): bool
    {
        // TODO: Implement delete() method.
    }

    public function sync(VirtualMachines $vm): VirtualMachines
    {
        // TODO: Implement sync() method.
    }

    public function listAll(): array
    {
        // TODO: Implement listAll() method.
    }

    public static function performCommand($command, Repositories|ComputeMembers $computeMember): ?array
    {
        try {
            if ($computeMember->is_management_agent_available == true) {
                return $computeMember->performAgentCommand($command);
            } else {
                $log = [
                    'command'   =>  $command,
                    'member'    =>  $computeMember->name
                ];

                $result = $computeMember->performSSHCommand($command);

                $log['output'] = $result['output'];
                $log['error'] = $result['error'];

                Log::debug(print_r($log, true));

                return $result;
            }
        } catch (CannotConnectWithSshException $exception) {
            Log::error(__METHOD__ . 'There is an error in performing the command: ' . $command .
                ' on the compute member: ' . $computeMember->name . '/' . $computeMember->uuid .
                '. The error is: ' . $exception->getMessage());

            Log::debug(__METHOD__ . ' Running the health check for the compute member: ' .
                $computeMember->name . '/' . $computeMember->uuid);

            throw $exception;
        } catch (\Exception $exception) {
            Log::error(__METHOD__ . 'There is an error in performing the command: ' . $command . '' .
                ' on the compute member: ' . $computeMember->name . '/' . $computeMember->uuid .
                '. The error is: ' . $exception->getMessage());

            throw $exception;
        }
    }
}
