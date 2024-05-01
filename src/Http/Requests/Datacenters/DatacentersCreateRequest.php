<?php

namespace NextDeveloper\IAAS\Http\Requests\Datacenters;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class DatacentersCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'slug' => 'nullable|string',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'maintenance_mode' => 'boolean',
        'geo_latitude' => 'string',
        'geo_longitude' => 'string',
        'tier_level' => 'nullable|integer',
        'total_capacity' => 'nullable',
        'guaranteed_uptime' => 'nullable',
        'is_carrier_neutral' => 'nullable|boolean',
        'power_source' => 'nullable|string',
        'ups' => 'nullable|string',
        'cooling' => 'nullable|string',
        'common_city_id' => 'nullable|exists:common_cities,uuid|uuid',
        'common_country_id' => 'nullable|exists:common_countries,uuid|uuid',
        'tags' => '',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE



}