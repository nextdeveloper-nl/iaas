<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'nullable|exists:iaas_virtual_machines,uuid|uuid',
        'cpu' => 'nullable|integer',
        'ram' => 'nullable|integer',
        'status' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}