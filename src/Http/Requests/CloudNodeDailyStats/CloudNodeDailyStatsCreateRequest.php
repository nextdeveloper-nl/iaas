<?php

namespace NextDeveloper\IAAS\Http\Requests\CloudNodeDailyStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class CloudNodeDailyStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'stat_date' => 'nullable|date',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'avg_vm_count' => 'nullable',
        'max_vm_count' => 'nullable|integer',
        'avg_vcpus' => 'nullable',
        'max_vcpus' => 'nullable|integer',
        'avg_ram_gb' => 'nullable',
        'max_ram_gb' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}