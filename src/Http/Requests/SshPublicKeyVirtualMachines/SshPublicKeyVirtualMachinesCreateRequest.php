<?php

namespace NextDeveloper\IAAS\Http\Requests\SshPublicKeyVirtualMachines;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class SshPublicKeyVirtualMachinesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'iam_ssh_public_key_id' => 'required|exists:iam_ssh_public_keys,uuid|uuid',
        'iaas_virtual_machine_id' => 'required|exists:iaas_virtual_machines,uuid|uuid',
        'deployed_at' => 'nullable|date',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}