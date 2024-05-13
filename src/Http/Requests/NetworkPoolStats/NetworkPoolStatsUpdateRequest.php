<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkPoolStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkPoolStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_network_pool_id' => 'nullable|exists:iaas_network_pools,uuid|uuid',
        'used_vlan' => 'integer',
        'used_vxlan' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}