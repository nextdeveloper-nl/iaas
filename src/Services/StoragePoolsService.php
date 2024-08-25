<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\Commons\Database\Models\Currencies;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractStoragePoolsService;

/**
 * This class is responsible from managing the data for StoragePools
 *
 * Class StoragePoolsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class StoragePoolsService extends AbstractStoragePoolsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getStoragePoolPricing(StoragePools $pool, $gb = 1)
    {
        $currency = Currencies::where('id', $pool->common_currency_id)->first();

        if(!$currency)
            throw new \Exception('I cannot calculate storage pool pricing because I dont have ' .
                'currency information in the object I am trying to calculate');

        return [
            'id'    =>  $pool->uuid,
            'name'  =>  $pool->name,
            'currency'  =>  $currency->code,
            'currency_id'   =>  $currency->uuid,
            'hourly_gb_price'  =>  $pool->price_pergb,
            'monthly_gb_price'  =>  $pool->price_pergb_month,
            'price_list'    =>  [
                [
                    'size'      =>  '10 GB',
                    'hourly'    =>  $pool->price_pergb * 10,
                    'monthly'   =>  $pool->price_pergb_month * 10
                ],
                [
                    'size'      =>  '100 GB',
                    'hourly'    =>  $pool->price_pergb * 100,
                    'monthly'   =>  $pool->price_pergb_month * 100
                ],
                [
                    'size'      =>  '250 GB',
                    'hourly'    =>  $pool->price_pergb * 250,
                    'monthly'   =>  $pool->price_pergb_month * 250
                ],
                [
                    'size'      =>  '500 GB',
                    'hourly'    =>  $pool->price_pergb * 500,
                    'monthly'   =>  $pool->price_pergb_month * 500
                ],
                [
                    'size'      =>  '1000 GB',
                    'hourly'    =>  $pool->price_pergb * 1000,
                    'monthly'   =>  $pool->price_pergb_month * 1000
                ],
                [
                    'size'      =>  '2000 GB',
                    'hourly'    =>  $pool->price_pergb * 2000,
                    'monthly'   =>  $pool->price_pergb_month * 2000
                ],
            ]
        ];
    }
}
