<?php

namespace NextDeveloper\IAAS\Http\Requests\DatacentersPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class DatacentersPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'slug' => 'nullable|string',
        'description' => 'nullable|string',
        'is_public' => 'nullable|boolean',
        'is_active' => 'nullable|boolean',
        'is_in_maintenance' => 'nullable|boolean',
        'geo_latitude' => 'nullable|string',
        'geo_longitude' => 'nullable|string',
        'tier_level' => 'nullable|integer',
        'total_capacity' => 'nullable',
        'guaranteed_uptime' => 'nullable',
        'is_carrier_neutral' => 'nullable|boolean',
        'power_source' => 'nullable|string',
        'ups' => 'nullable|string',
        'cooling' => 'nullable|string',
        'city_name' => 'nullable|string',
        'country_name' => 'nullable|string',
        'cloud_nodes_count' => 'nullable|integer',
        'compute_pools_count' => 'nullable|integer',
        'storage_pools_count' => 'nullable|integer',
        'network_pools_count' => 'nullable|integer',
        'tags' => 'nullable',
        'datacenter_maintainer' => 'nullable|string',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}