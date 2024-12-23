<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotContinueException;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action exports the virtual machine
 */
class Export extends AbstractAction
{
    public const EVENTS = [
        'exporting:NextDeveloper\IAAS\VirtualMachines',
        'exported:NextDeveloper\IAAS\VirtualMachines',
        'export-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public const PARAMS = [
        'iaas_repository_id'    =>  'required|exists:iaas_repositories,id'
    ];

    /**
     * @var Repositories
     */
    private $repository;

    public function __construct(VirtualMachines $vm, $params)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct($params);

        $this->repository = Repositories::where('uuid', $params['iaas_repository_id'])->first();

        if($this->repository->is_public == false || $this->repository->iam_account_id != UserHelper::currentAccount()->id)
            throw new NotAllowedException('This repository is not public nor its yours. That is why' .
                ' you cannot export the virtual machine to this repository.');

        if($this->model->status != 'halted')
            throw new CannotContinueException('You can only export a halted virtual machine. Make sure' .
                ' that you need to shutdown this virtual machine before taking an export. Or you can basicly' .
                ' take snapshot, convert that into a proper virtual machine and then export. Beware that' .
                ' this approach requires exactly the same resource, with this VM and a snapshot which' .
                ' may increase you invoice. If you select this approach, make sure that you delete new' .
                ' virtual machine and snapshot after export.');
    }

    public function handle()
    {
        $this->setProgress(0, 'Export of virtual machine started');
        Events::fire('exporting:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            Events::fire('export-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            Events::fire('export-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);

        if(!array_key_exists('power-state', $vmParams)) {
            //  The VM must not be available to be honest. So we should make a health check here.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            $job = new HealthCheck($this->model, null, $this);
            $id = $job->getActionId();

            dispatch($job)->onQueue('iaas');

            $this->setProgress(100, 'Checking the health of the VM. ' .
                'We suspect something is happening to it.');

            Events::fire('export-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);

            return $id;
        }

        if($vmParams['power-state'] != 'halted') {
            $this->setFinishedWithError('We cannot export the virtual machine. It is not halted.');
            Events::fire('export-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $exported = VirtualMachinesXenService::export($this->model, $this->repository);

        $this->model->status = 'initiated';
        $this->model->save();

        Events::fire('exported:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setProgress(100, 'Virtual machine initiated');
    }
}
