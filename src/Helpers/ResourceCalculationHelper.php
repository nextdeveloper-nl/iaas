<?php

namespace NextDeveloper\IAAS\Helpers;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class ResourceCalculationHelper
{
    //  Our default ram unit in whole project is GB. That is why we are making all the calculations in terms of GB
    public static function getActualRam($ram) : float|int
    {
        if($ram == intval($ram)) {
            return $ram;
        }

        $ram = strtolower($ram);

        if(Str::contains($ram, 'mb')) {
            $ram = str_replace('mb', '', $ram);
            $ram = trim($ram);
            $ram = int($ram);
            return $ram / 1024;
        }

        if(Str::contains($ram, 'gb')) {
            $ram = str_replace('gb', '', $ram);
            $ram = trim($ram);
            $ram = int($ram);
            return $ram;
        }

        if(Str::contains($ram, 'tb')) {
            $ram = str_replace('tb', '', $ram);
            $ram = trim($ram);
            $ram = int($ram);
            return $ram * 1024;
        }

        return 0;
    }

    /**
     * Our default unit for ram is GB, that is why we first convert the ram size into GB here then we return
     * back to MB. The reason that we are doing this is to provide more precision of administrator to use rams in MB
     * instead of GB in virtual machines.
     *
     * @param $ram
     * @return int
     */
    public static function getRamInMb($ram) : int
    {
        $ram = self::getActualRam($ram);

        return $ram * 1024;
    }

    /**
     * Returns the number of CPU core which can be provided against the ram
     * @param ComputePools $cp
     * @param $ram
     * @return float|int|void
     */
    public static function getCpuPerRam($ram, ComputePools $cp = null) {
        // Modifying the data before creating the record
        //  Here we will bring CPU/RAM ratio later to fix this issue
        //  - we need to bring max_cpu_per_vm field also in the compute_pools
        //  - we need to bring min_cpu_per_vm field also in the compute_pools
        //  If there is a CPU/RAM ratio (!= null) then we will make the calculation, if we dont we dont.

        if($ram <= 2)
            return 2;

        if($ram <= 32)
            return $ram / 2;

        return 16;
    }

    public static function getAvailableRamSizes(ComputePools $cp) {
        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_pool_id', $cp->id)
            ->orderBy('free_ram', 'desc')
            ->first();

        $ramSizes = [];
        //  Here we are using the power of 2, but we should be getting this value from the compute pool later!
        for($gb = 1; $gb < $computeMembers->free_ram; $gb = $gb * 2){
            $ramSizes[] = $gb;
        }

        return $ramSizes;
    }

    public static function getAvailableDiskSizes(ComputePools $cp, $minSize = 10) {
        if($cp->pool_type == 'star') {
            $diskSizes = [];

            for ($i = $minSize; $i <= 2000; $i = $i + 10) {
                $diskSizes[] = $i;
            }

            return $diskSizes;
        }

        if($cp->pool_type == 'one') {
            $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_pool_id', $cp->id)
                ->orderBy('free_ram', 'desc')
                ->first();

            $diskSizes = [];
            //  Here we are using the power of 2, but we should be getting this value from the compute pool later!
            for($gb = 1; $gb < $computeMembers->free_ram; $gb = $gb * 2){
                $diskSize = $gb * $cp->disk_ram_ratio;

                if($diskSize < $minSize)
                    continue;

                $diskSizes[] = $diskSize;
            }

            return $diskSizes;
        }

        return [];
    }

    public static function getDiskSizeAgainstRam(ComputePools $cp, $ram) {
        $ram = self::getActualRam($ram);

        return $cp->disk_ram_ratio * $ram;
    }

    public static function getAvailableDiskSizesForComputeMember(ComputeMembers $cm)
    {
        $cp = ComputeMembersService::getComputePool($cm);

        if($cp->pool_type == 'star') {
            return self::getAvailableDiskSizes($cp);
        }

        $diskSizes = [];

        //  Here we are using the power of 2, but we should be getting this value from the compute pool later!
        for($gb = 1; $gb < $cm->free_ram; $gb = $gb * 2){
            $diskSizes[] = $gb * $cp->disk_ram_ratio;
        }

        return $diskSizes;
    }
}
