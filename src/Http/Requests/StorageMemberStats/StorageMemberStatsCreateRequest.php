<?php

namespace NextDeveloper\IAAS\Http\Requests\StorageMemberStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StorageMemberStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_storage_member_id' => 'required|exists:iaas_storage_members,uuid|uuid',
        'used_disk' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}