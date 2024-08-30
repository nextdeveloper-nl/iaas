<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualMachineStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualMachineStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'cpu' => 'required|integer',
        'ram' => 'required|integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}