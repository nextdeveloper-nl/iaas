<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;

/**
 * This action unplugs the virtual machine, and then plugs it back in
 */
class EjectCd extends AbstractAction
{
    public const EVENTS = [
        'mounting-cd:NextDeveloper\IAAS\VirtualMachines',
        'cd-mounted:NextDeveloper\IAAS\VirtualMachines',
        'mounting-cd-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm, $params)
    {
        $this->model = $vm;

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Mounting CD to virtual machine');

        Events::fire('ejecting-cd:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $result = VirtualMachinesXenService::unmountCD($this->model);

        if($result) {
            Events::fire('cd-ejected:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->setFinished('CD ejected');
            return;
        }

        Events::fire('cd-eject-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setFinishedWithError('CD cannot be ejected');
    }
}
