<?php

namespace NextDeveloper\IAAS\Http\Requests\CloudNodesPerformance;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class CloudNodesPerformanceUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'is_active' => 'nullable|boolean',
        'is_alive' => 'nullable|boolean',
        'is_in_maintenance' => 'nullable|boolean',
        'datacenter_name' => 'nullable|string',
        'vm_count' => 'nullable|integer',
        'compute_vcpu_total' => 'nullable|integer',
        'compute_vcpu_used' => 'nullable|integer',
        'compute_vcpu_pct' => 'nullable',
        'compute_vcpu_health' => 'nullable|string',
        'compute_alarm_count' => 'nullable|integer',
        'memory_total_gb' => 'nullable',
        'memory_used_gb' => 'nullable',
        'memory_pct' => 'nullable',
        'memory_health' => 'nullable|string',
        'storage_total_gb' => 'nullable|integer',
        'storage_used_gb' => 'nullable|integer',
        'storage_pct' => 'nullable',
        'storage_health' => 'nullable|string',
        'storage_alarm_count' => 'nullable|integer',
        'network_alarm_count' => 'nullable|integer',
        'network_health' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}