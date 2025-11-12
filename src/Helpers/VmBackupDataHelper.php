<?php

namespace NextDeveloper\IAAS\Helpers;

use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;

class VmBackupDataHelper
{
    public function __construct(public VirtualMachineBackups $backup)
    {

    }

    public function setData($key, $default)
    {
        $data = $this->backup->data;

        $data[$key] = $default;

        $this->backup->update([
            'data' => $data
        ]);

        return $default;
    }

    public function getData($key, $default = null) : array
    {
        if(array_key_exists($key, $this->backup->data)) {
            return $this->backup->data[$key];
        }

        $this->setData($key, $default);

        return $default;
    }
}
