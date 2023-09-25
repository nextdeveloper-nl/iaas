<?php

namespace NextDeveloper\IAAS\Http\Requests\Datacenters;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class DatacentersUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name'               => 'nullable|string|max:100',
        'slug'               => 'nullable|string|max:100',
        'is_public'          => 'boolean',
        'is_active'          => 'boolean',
        'maintenance_mode'   => 'boolean',
        'geo_latitude'       => 'string|max:100',
        'geo_longitude'      => 'string|max:100',
        'tier_level'         => 'nullable',
        'total_capacity'     => 'nullable|integer',
        'guaranteed_uptime'  => 'nullable|numeric',
        'is_carrier_neutral' => 'nullable|boolean',
        'power_source'       => 'nullable',
        'ups'                => 'nullable',
        'cooling'            => 'nullable',
        'city'               => 'nullable|string|max:50',
        'iam_account_id'     => 'nullable|exists:iam_accounts,uuid|uuid',
        'common_country_id'  => 'nullable|exists:common_countries,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}