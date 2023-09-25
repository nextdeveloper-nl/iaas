<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;

/**
* This class is responsible from managing the data for VirtualMachines
*
* Class VirtualMachinesService.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class VirtualMachinesService extends AbstractVirtualMachinesService {

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static $availableOperations = [
        'start',
        'reboot',
        'shutdown'
    ];

    /**
     * Starts, boots the virtual machine
     *
     * @param VirtualMachines $vm Virtual Machine
     * @return VirtualMachines
     */
    public static function start(VirtualMachines $vm) : VirtualMachines {

    }

    /**
     * Reboots the virtual machine
     *
     * @param VirtualMachines $vm Virtual Machine
     * @param $forceReboot
     * @return VirtualMachines
     */
    public static function reboot(VirtualMachines $vm, $forceReboot = false) : VirtualMachines {

    }

    /**
     * Shutsdown the virtual machine
     *
     * @param VirtualMachines $vm Virtual Machine
     * @param $forceShutdown
     * @return VirtualMachines
     */
    public static function shutdown(VirtualMachines $vm, $forceShutdown = false) : VirtualMachines {

    }

    public static function create($data) : VirtualMachines {
        return parent::create($data);
    }

    /**
     * Updated the virtual machine
     *
     * @param $vmId
     * @param $data
     * @return VirtualMachines
     * @throws \Exception
     */
    public static function update($vmId, $data) : VirtualMachines {
        parent::update($vmId, $data);
    }
}