<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;

class HypervisorEvacuationXenService extends AbstractXenService
{
    /**
     * Returns all non-template, non-control-domain VMs on a compute member.
     */
    public static function getVmsOnComputeMember(ComputeMembers $computeMember): array
    {
        $command = 'xe vm-list is-control-domain=false is-a-template=false params=uuid,name-label,power-state';
        $result = self::performCommand($command, $computeMember);

        return self::parseListResult($result['output']);
    }

    /**
     * Returns the uuid of the first NFS SR found on the compute member, or null if none.
     */
    public static function getNfsSrOnComputeMember(ComputeMembers $computeMember): ?string
    {
        $command = 'xe sr-list type=nfs params=uuid';
        $result = self::performCommand($command, $computeMember);

        $parsed = self::parseListResult($result['output']);

        foreach ($parsed as $item) {
            if (!empty($item['uuid'])) {
                return trim($item['uuid']);
            }
        }

        return null;
    }

    /**
     * Returns the NFS SR mount path on the hypervisor.
     * XenServer mounts SRs under /var/run/sr-mount/<sr-uuid>.
     */
    public static function getSrMountPath(string $srUuid, ComputeMembers $computeMember): string
    {
        return '/var/run/sr-mount/' . $srUuid;
    }

    /**
     * Returns full VM params for a given VM uuid.
     */
    public static function getVmParams(string $vmUuid, ComputeMembers $computeMember): array
    {
        $command = 'xe vm-param-list uuid=' . $vmUuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    /**
     * Returns VBD list for a VM (excludes CDROMs implicitly — caller should check type).
     */
    public static function getVbdsForVm(string $vmUuid, ComputeMembers $computeMember): array
    {
        $command = 'xe vbd-list vm-uuid=' . $vmUuid . ' params=uuid,vdi-uuid,device,type,bootable';
        $result = self::performCommand($command, $computeMember);

        return self::parseListResult($result['output']);
    }

    /**
     * Returns VDI params for a given VDI uuid.
     */
    public static function getVdiParams(string $vdiUuid, ComputeMembers $computeMember): array
    {
        $command = 'xe vdi-param-list uuid=' . $vdiUuid;
        $result = self::performCommand($command, $computeMember);

        return self::parseResult($result['output']);
    }

    /**
     * Returns VIF list for a VM.
     */
    public static function getVifsForVm(string $vmUuid, ComputeMembers $computeMember): array
    {
        $command = 'xe vif-list vm-uuid=' . $vmUuid . ' params=uuid,device,network-uuid,mac,mtu';
        $result = self::performCommand($command, $computeMember);

        return self::parseListResult($result['output']);
    }

    /**
     * Walks the VHD parent chain for a given leaf VHD path.
     * Returns an array of absolute file paths: [leaf, parent, grandparent, ...].
     *
     * vhd-util query -n <file> -p prints the parent path, or returns a non-zero exit
     * with "has no parent" in output when the root is reached.
     */
    public static function getVhdChain(string $leafVhdPath, ComputeMembers $computeMember): array
    {
        $chain = [$leafVhdPath];
        $current = $leafVhdPath;

        for ($depth = 0; $depth < 64; $depth++) {
            $command = 'vhd-util query -n ' . escapeshellarg($current) . ' -p 2>&1';
            $result = self::performCommand($command, $computeMember);

            $output = trim($result['output'] ?? '');

            if (
                empty($output)
                || str_contains($output, 'has no parent')
                || str_contains($output, 'no parent')
                || str_contains($output, 'error')
            ) {
                break;
            }

            $chain[] = $output;
            $current = $output;
        }

        return $chain;
    }

    /**
     * Exports VM metadata (no disk data) to a file on the source host.
     * The resulting .xva file can be imported on the target host after disks are present.
     */
    public static function exportVmMetadata(
        string $vmUuid,
        string $exportPath,
        ComputeMembers $computeMember
    ): bool {
        $command = 'xe vm-export uuid=' . $vmUuid . ' filename=' . escapeshellarg($exportPath) . ' metadata=true';
        $result = self::performCommand($command, $computeMember);

        if (!empty($result['error'])) {
            Log::error(__METHOD__ . ' | Metadata export failed for VM ' . $vmUuid . ': ' . $result['error']);
            return false;
        }

        Log::info(__METHOD__ . ' | Exported VM metadata to: ' . $exportPath);

        return true;
    }

    /**
     * Imports VM metadata on the target host. VDIs must already be present in the SR
     * (via sr-scan) before calling this. Returns the new VM uuid or null on failure.
     */
    public static function importVmMetadata(
        string $importPath,
        string $srUuid,
        ComputeMembers $computeMember
    ): ?string {
        $command = 'xe vm-import filename=' . escapeshellarg($importPath) . ' sr-uuid=' . $srUuid;
        $result = self::performCommand($command, $computeMember);

        if (!empty($result['error'])) {
            Log::error(__METHOD__ . ' | Metadata import failed: ' . $result['error']);
            return null;
        }

        $newVmUuid = trim($result['output']);

        Log::info(__METHOD__ . ' | Imported VM with new UUID: ' . $newVmUuid);

        return $newVmUuid;
    }

    /**
     * Copies VHD files from the source SR path to the target SR path using rsync over SSH.
     * This command runs on the SOURCE host and pushes to the target.
     *
     * Prerequisite: passwordless SSH (key-based auth) must be configured
     * from the source host to the target host.
     */
    public static function syncVhdFiles(
        array $vhdFiles,
        string $sourceSrPath,
        string $targetSrPath,
        ComputeMembers $source,
        ComputeMembers $target
    ): bool {
        $targetIp = $target->ip_addr;
        $targetPort = $target->ssh_port ?? 22;

        foreach ($vhdFiles as $vhdFile) {
            $basename = basename($vhdFile);
            $sourceFile = $sourceSrPath . '/' . $basename;
            $targetDest = $targetIp . ':' . $targetSrPath . '/';

            $command = 'rsync -avz --progress'
                . ' -e "ssh -o StrictHostKeyChecking=no -o BatchMode=yes -p ' . (int) $targetPort . '"'
                . ' ' . escapeshellarg($sourceFile)
                . ' ' . escapeshellarg($targetDest);

            Log::info(__METHOD__ . ' | Syncing VHD: ' . $sourceFile . ' -> ' . $targetDest);

            $result = self::performCommand($command, $source);

            //  rsync exits 0 on success; any error in stderr means failure
            if (!empty($result['error']) && !str_contains($result['output'], 'sent')) {
                Log::error(__METHOD__ . ' | rsync failed for ' . $basename . ': ' . $result['error']);
                return false;
            }

            Log::info(__METHOD__ . ' | Synced: ' . $basename);
        }

        return true;
    }

    /**
     * Transfers the VM metadata XVA file from source host to target host using rsync over SSH.
     * Runs on the SOURCE host and pushes to the target.
     */
    public static function syncMetadataFile(
        string $sourceFilePath,
        string $targetFilePath,
        ComputeMembers $source,
        ComputeMembers $target
    ): bool {
        $targetPort = $target->ssh_port ?? 22;

        $command = 'rsync -avz'
            . ' -e "ssh -o StrictHostKeyChecking=no -o BatchMode=yes -p ' . (int) $targetPort . '"'
            . ' ' . escapeshellarg($sourceFilePath)
            . ' ' . escapeshellarg($target->ip_addr . ':' . $targetFilePath);

        $result = self::performCommand($command, $source);

        if (!empty($result['error']) && !str_contains($result['output'], 'sent')) {
            Log::error(__METHOD__ . ' | Failed to transfer metadata file: ' . $result['error']);
            return false;
        }

        return true;
    }

    /**
     * Triggers an SR scan on the given compute member so newly-copied VHDs are registered.
     */
    public static function rescanSr(string $srUuid, ComputeMembers $computeMember): void
    {
        $command = 'xe sr-scan uuid=' . $srUuid;
        self::performCommand($command, $computeMember);

        Log::info(__METHOD__ . ' | SR scan triggered for: ' . $srUuid);
    }

    /**
     * Waits until a VM reaches the expected power state, polling every 5 seconds.
     * Returns true when the state is reached, false on timeout.
     */
    public static function waitForPowerState(
        string $vmUuid,
        string $expectedState,
        ComputeMembers $computeMember,
        int $maxAttempts = 24
    ): bool {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            sleep(5);

            $command = 'xe vm-param-get uuid=' . $vmUuid . ' param-name=power-state';
            $result = self::performCommand($command, $computeMember);
            $currentState = trim($result['output'] ?? '');

            if ($currentState === $expectedState) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes the temporary metadata export file from the host.
     */
    public static function cleanupMetadataFile(string $filePath, ComputeMembers $computeMember): void
    {
        $command = 'rm -f ' . escapeshellarg($filePath);
        self::performCommand($command, $computeMember);
    }

    /**
     * performCommand proxy — follows the same pattern as VirtualMachinesXenService.
     */
    public static function performCommand($command, ComputeMembers $computeMember): ?array
    {
        try {
            if ($computeMember->is_management_agent_available == true) {
                return $computeMember->performAgentCommand($command);
            }

            $result = $computeMember->performSSHCommand($command);

            Log::debug(__METHOD__ . ' [' . $computeMember->name . '] cmd: ' . $command
                . ' | out: ' . $result['output']
                . ' | err: ' . $result['error']);

            return $result;
        } catch (CannotConnectWithSshException $e) {
            Log::error(__METHOD__ . ' | SSH connection failed to '
                . $computeMember->name . ': ' . $e->getMessage());

            throw $e;
        }
    }
}
