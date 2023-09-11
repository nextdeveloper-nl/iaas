<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputePools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputePoolsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name'                    => 'required|string|max:100',
        'resource_validator'      => 'nullable|string|max:500',
        'pool_type'               => 'nullable',
        'pool_data'               => 'nullable',
        'management_type'         => '',
        'virtualization'          => '',
        'virtualization_version'  => 'nullable|string|max:10',
        'provisioning_alg'        => 'nullable|string|max:255',
        'management_package_name' => 'nullable|string|max:200',
        'is_active'               => 'boolean',
        'is_alive'                => 'boolean',
        'is_public'               => 'boolean',
        'iaas_datacenter_id'      => 'nullable|exists:iaas_datacenters,uuid|uuid',
        'iaas_cloud_node_id'      => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
        'iam_account_id'          => 'required|exists:iam_accounts,uuid|uuid',
        'iam_user_id'             => 'required|exists:iam_users,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}