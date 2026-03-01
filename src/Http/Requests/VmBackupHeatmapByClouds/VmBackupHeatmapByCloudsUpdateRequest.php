<?php

namespace NextDeveloper\IAAS\Http\Requests\VmBackupHeatmapByClouds;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VmBackupHeatmapByCloudsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'cloud_node_name' => 'nullable|string',
        'iaas_datacenter_id' => 'nullable|exists:iaas_datacenters,uuid|uuid',
        'datacenter_name' => 'nullable|string',
        'backup_date' => 'nullable|date',
        'day_offset' => 'nullable|integer',
        'day_of_week' => 'nullable|string',
        'distinct_jobs' => 'nullable|integer',
        'rpo_breach_count' => 'nullable|integer',
        'day_status' => 'nullable|string',
        'total_runs' => 'nullable|integer',
        'success_runs' => 'nullable|integer',
        'failed_runs' => 'nullable|integer',
        'day_size_bytes' => 'nullable|integer',
        'avg_duration_secs' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}