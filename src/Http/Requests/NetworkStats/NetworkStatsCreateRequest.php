<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworkStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworkStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_network_id' => 'required|exists:iaas_networks,uuid|uuid',
        'total_tx' => 'integer',
        'total_rx' => 'integer',
        'total_ip_address' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}