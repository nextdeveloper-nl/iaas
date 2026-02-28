<?php

namespace NextDeveloper\IAAS\Http\Requests\KpiPerformance;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class KpiPerformanceUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'active_clouds' => 'nullable|integer',
        'active_clouds_delta' => 'nullable|integer',
        'active_clouds_delta_pct' => 'nullable',
        'compute_vcpus' => 'nullable|integer',
        'compute_vcpus_delta' => 'nullable|integer',
        'compute_vcpus_delta_pct' => 'nullable',
        'storage_pb' => 'nullable',
        'storage_pb_delta' => 'nullable',
        'storage_pb_delta_pct' => 'nullable',
        'active_tenants' => 'nullable|integer',
        'active_tenants_delta' => 'nullable|integer',
        'active_tenants_delta_pct' => 'nullable',
        'alarm_count' => 'nullable|integer',
        'alarm_count_delta' => 'nullable|integer',
        'alarm_count_delta_pct' => 'nullable',
        'alarm_critical_count' => 'nullable|integer',
        'alarm_high_count' => 'nullable|integer',
        'alarm_low_count' => 'nullable|integer',
        'alarm_compute_members_count' => 'nullable|integer',
        'alarm_storage_members_count' => 'nullable|integer',
        'alarm_network_members_count' => 'nullable|integer',
        'alarm_virtual_machines_count' => 'nullable|integer',
        'bandwidth_gbps' => 'nullable',
        'bandwidth_gbps_delta' => 'nullable',
        'bandwidth_gbps_delta_pct' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}