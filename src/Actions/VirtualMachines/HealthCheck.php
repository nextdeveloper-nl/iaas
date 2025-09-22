<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotConnectWithSshException;
use NextDeveloper\IAAS\Helpers\IaasHelper;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action converts the virtual machine into a template
 */
class HealthCheck extends AbstractAction
{
    public const EVENTS = [
        'checking:NextDeveloper\IAAS\VirtualMachines',
        'checked:NextDeveloper\IAAS\VirtualMachines',
        'healthy:NextDeveloper\IAAS\VirtualMachines',
        'stopped:NextDeveloper\IAAS\VirtualMachines',
        'halted:NextDeveloper\IAAS\VirtualMachines',
        'running:NextDeveloper\IAAS\VirtualMachines',
        'paused:NextDeveloper\IAAS\VirtualMachines',
        'health-check-failed:NextDeveloper\IAAS\VirtualMachines',
        'vm-is-lost:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm, $params = null, $previous = null)
    {
        $this->queue = 'iaas-health-check';

        $this->model = $vm;

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Virtual machine health check started');
        Events::fire('checking:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(10, 'Marking the server as checking health');

        if($this->model->is_draft) {
            $this->setFinished('Virtual machine is a draft. Skipping health check.');
            CommentsService::createSystemComment('Virtual machine is in draft state. Skipping health check.', $this->model);
            return;
        }

        if($this->model->is_lost) {
            $this->setFinished('Virtual machine is lost. Skipping health check.');
            CommentsService::createSystemComment('Virtual machine is lost. Skipping health check.', $this->model);
            return;
        }

        if(
            $this->model->is_draft &&
            $this->model->created_at->isBefore(
                Carbon::now()->subMinutes(15)
            )) {
            //  If the virtual machine is in draft position for more than 15 minutes. We are deleting it.
            $this->model->delete();

            Events::fire('cleaned-up', $this->model);
            return;
        }

        $this->model->status = 'checking-health';
        $this->model->save();

        $this->setProgress(25, 'Checking the environment of the virtual machine.');
        $computeMember = VirtualMachinesService::getComputeMember($this->model);

        if(!$computeMember->is_alive) {
            Log::error(__METHOD__ . ' | The compute member is not alive: ' . $computeMember->uuid);

            Events::fire('health-check-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);

            CommentsService::createSystemComment('Virtual machine health check failed because compute members seems like not alive.', $this->model);

            IaasHelper::notifyObjectOwner('Virtual machine with id: ' . $this->model->uuid . ' is lost.' , $computeMember);

            StateHelper::setState($this->model, 'unhealthy', 'The compute member is not alive');

            Events::fire('marked-as-lost:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->model->update([
                'is_lost'   =>  true,
                'deleted_at' => Carbon::now(),
                'status'    =>  'lost'
            ]);

            $this->setFinishedWithError('Virtual machine health check failed. Please consult to your' .
                ' administrator for more information or create a support ticket to resolve this issue.');
        }

        $this->setProgress(50, 'Checking if the virtual machine is alive');

        try {
            $isVmThere = VirtualMachinesXenService::checkIfVmIsThere($this->model);
        } catch (CannotConnectWithSshException $exception) {
            Log::error(__METHOD__ . ' | Cannot connect with SSH to the VM: ' . $this->model->uuid);

            $this->setFinishedWithError('Virtual machine health check failed. Please consult to your' .
                ' administrator for more information or create a support ticket to resolve this issue.');
            Events::fire('health-check-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        Log::info(__METHOD__ . ' | We checked if VM (' . $this->model->uuid
            . ' exists, and the result is: ' . ($isVmThere ? 'TRUE' : 'FALSE'));

        if(!$isVmThere) {
            //  This means that the VM is not there. Lets check if its already dead or not;

            //  Oops the VM is lost! We should mark it as lost
            $this->model->is_lost = true;
            $this->model->status = 'lost';
            $this->model->deleted_at = now();
            $this->model->save();

            Log::info(__METHOD__ . ' | Marked VM as lost: ' . $this->model->name);

            Events::fire('vm-is-lost:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->setProgress(100, 'Virtual machine marked as lost.');
            return;
        }

        $vmParams = VirtualMachinesXenService::getVmParameters($this->model);
        $consoleParams = VirtualMachinesXenService::getConsoleParameters($this->model);

        $this->setProgress(75, 'Marking the server power state as: ' . $vmParams['power-state']);

        $this->model->updateQuietly([
            'status'    =>  $vmParams['power-state'],
            'console_data'  =>  $consoleParams[0]
        ]);

        switch ($vmParams['power-state']) {
            case 'running':
                Events::fire('running:NextDeveloper\IAAS\VirtualMachines', $this->model);
                break;
            case 'halted':
                Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);
                break;
            case 'paused':
                Events::fire('paused:NextDeveloper\IAAS\VirtualMachines', $this->model);
                break;
        }

        Events::fire('healthy:NextDeveloper\IAAS\VirtualMachines', $this->model);
        Events::fire('checked:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setProgress(100, 'Virtual machine health check finished');
    }
}
