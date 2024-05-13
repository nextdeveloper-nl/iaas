<?php

namespace NextDeveloper\IAAS\Http\Requests\StorageVolumeStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class StorageVolumeStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_storage_volume_id' => 'nullable|exists:iaas_storage_volumes,uuid|uuid',
        'used_disk' => 'integer',
        'free_disk' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}