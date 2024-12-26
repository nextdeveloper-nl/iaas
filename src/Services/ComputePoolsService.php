<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\Models\Currencies;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractComputePoolsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for ComputePools
 *
 * Class ComputePoolsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class ComputePoolsService extends AbstractComputePoolsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getPricingTable(ComputePools $computePool) : array {
        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_pool_id', $computePool->id)
            ->orderBy('free_ram', 'desc')
            ->first();

        if(!$computeMembers)
            return [];

        $isServerAvailable = true;

        $priceTable = [];

        $currency = Currencies::where('id', $computePool->common_currency_id)->first();

        for($gb = 1; $gb < $computeMembers->free_ram; $gb = $gb * 2){
            $cpu = 2;

            if($gb > 2 && $gb <= 32)
                $cpu = $gb / 2;

            if($gb >= 32)
                $cpu = 8;

            $priceTable[] = [
                'name'  =>  $computePool->code_name . ' ' . $gb,
                'ram'   =>  $gb,
                'cpu'   =>  $cpu,
                'disk'  =>  $computePool->disk_ram_ratio * $gb,
                'monthly'   =>  $computePool->price_pergb_month * $gb . ' ' . $currency->code,
                'hourly'    =>  $computePool->price_pergb * $gb . ' ' . $currency->code
            ];

            if($gb > 1024)
                break;
        }
        /**
         * Burada free ram'i olabilecek en yüksek kapasiteli makinasına bakacağız.
         * 1 2 4 8 16 32 64 128
         */
        return $priceTable;
    }

    public static function getComputeMembers(ComputePools $pool) : Collection
    {
        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_pool_id', $pool->id)
            ->get();
    }
}
