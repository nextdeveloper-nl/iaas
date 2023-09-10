<?php

namespace NextDeveloper\IAAS\Http\Requests\sStoragePools;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class sStoragePoolsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules() {
        return [
            'name'               => 'nullable|string|max:100',
			'gb_per_hour_price'  => 'nullable|numeric',
			'is_active'          => 'boolean',
			'iaas_cloud_node_id' => 'nullable|exists:iaas_cloud_nodes,uuid|uuid',
			'iam_account_id'     => 'nullable|exists:iam_accounts,uuid|uuid',
			'iam_user_id'        => 'nullable|exists:iam_users,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n
}