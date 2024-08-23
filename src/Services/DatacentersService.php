<?php

namespace NextDeveloper\IAAS\Services;

use Exceptions\DuplicateObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Database\Models\Countries;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractDatacentersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for Datacenters
 *
 * Class DatacentersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class DatacentersService extends AbstractDatacentersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function getComputePoolPricing(Datacenters $datacenters = null)
    {
        if($datacenters) {
            $computePools = ComputePools::withoutGlobalScope(LimitScope::class)
                ->where('iaas_datacenter_id', $datacenters->id)
                ->get();
        } else {
            $computePools = ComputePools::withoutGlobalScope(LimitScope::class)
                ->get();
        }

        $prices = [];

        foreach ($computePools as $computePool) {
            $prices[$computePool->name][] = ComputePoolsService::getPricingTable($computePool);
        }

        return $prices;
    }

    public static function getByCountry() : array
    {
        $dcs = Datacenters::withoutGlobalScope(LimitScope::class)
            ->get();

        $list = [];

        foreach ($dcs as $dc) {
            if(!$dc->common_country_id)
                continue;

            $country = Countries::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $dc->common_country_id)
                ->first();

            $list[$country->name][] = [
                'id'    =>  $dc->uuid,
                'name'  =>  $dc->name
            ];
        }

        return $list;
    }

    public static function create($data)
    {
        $datacenter = Datacenters::withoutGlobalScopes()
            ->where('name', $data['name'])
            ->first();

        if($datacenter) {
            throw new DuplicateObject('Datacenter already exists. Please provide a different name.');
        }

        if(!array_key_exists('slug', $data)) {
            $data['slug'] = Str::slug($data['name']);
        }

        if($data['slug'] == '')
        {
            $data['slug'] = Str::slug($data['name']);
        }

        return parent::create($data);
    }
}
