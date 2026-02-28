<?php

namespace NextDeveloper\IAAS\Http\Requests\CloudNodeHourlyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class CloudNodeHourlyStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'stat_hour' => 'nullable|date',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'vm_count' => 'nullable|integer',
        'total_vcpus' => 'nullable|integer',
        'total_ram_gb' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}