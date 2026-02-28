<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachinesPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachinesPerspectiveUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'description' => 'nullable|string',
        'hostname' => 'nullable|string',
        'username' => 'nullable|string',
        'os' => 'nullable|string',
        'distro' => 'nullable|string',
        'version' => 'nullable|string',
        'domain_type' => 'nullable|string',
        'status' => 'nullable|string',
        'cpu' => 'nullable|integer',
        'ram' => 'nullable|integer',
        'last_metadata_request' => 'nullable|date',
        'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'cloud_node' => 'nullable|string',
        'common_domain_id' => 'nullable|exists:common_domains,uuid|uuid',
        'domain' => 'nullable|string',
        'disk_count' => 'nullable|integer',
        'network_card_count' => 'nullable|integer',
        'has_warnings' => 'nullable|integer',
        'has_errors' => 'nullable|integer',
        'number_of_disks' => 'nullable|integer',
        'total_disk_size' => 'nullable|integer',
        'network' => 'nullable|string',
        'ip_addr' => 'nullable',
        'states' => 'nullable',
        'pool_type' => 'nullable|string',
        'is_snapshot_available' => 'nullable|boolean',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        'compute_member_name' => 'nullable|string',
        'tags' => 'nullable',
        'is_template' => 'nullable|boolean',
        'is_draft' => 'nullable|boolean',
        'is_lost' => 'nullable|boolean',
        'is_locked' => 'nullable|boolean',
        'is_snapshot' => 'nullable|boolean',
        'auto_backup_interval' => 'nullable|string',
        'auto_backup_time' => 'nullable|string',
        'post_boot_script' => 'nullable|string',
        'maintainer' => 'nullable|string',
        'responsible' => 'nullable|string',
        'iaas_compute_pool_id' => 'nullable|exists:iaas_compute_pools,uuid|uuid',
        'snapshot_of_virtual_machine' => 'nullable|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}