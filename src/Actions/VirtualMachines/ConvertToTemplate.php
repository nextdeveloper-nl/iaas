<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Exceptions\CannotContinueException;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action converts the virtual machine into a template
 */
class ConvertToTemplate extends AbstractAction
{
    public const EVENTS = [
        'converting-to-template:NextDeveloper\IAAS\VirtualMachines',
        'converted-to-template:NextDeveloper\IAAS\VirtualMachines',
        'conversion-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public const PARAMS = [
        'iaas_repository_id'    =>  'required|exists:iaas_repositories,uuid'
    ];

    /**
     * @var Repositories
     */
    private $repository;

    public function __construct(VirtualMachines $vm, $params)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        $params = $params[0];

        parent::__construct($params);

        $this->repository = Repositories::where('uuid', $params['iaas_repository_id'])->first();

        if($this->repository->is_public == false && $this->repository->iam_account_id != UserHelper::currentAccount()->id)
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
        $this->setProgress(0, 'Conversion to template is started.');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
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

            return $id;
        }

        if($vmParams['power-state'] != 'halted') {
            $this->setFinishedWithError('We cannot convert the virtual machine to template. It is not halted.');
            Events::fire('conversion-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->setProgress(20, 'Conversion checks made. Everything is fine, going for export.');

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->model->iaas_compute_member_id)
            ->first();

        $this->setProgress(40, 'Mounting the repository.');

        $isMounted = ComputeMemberXenService::mountVmRepository($computeMember, $this->repository);

        if(!$isMounted) {
            $this->setFinishedWithError('We cannot mount the given repository, that is why we cannot' .
                ' convert this virtual machine into a template.');
            Events::fire('conversion-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->setProgress(60, 'Exporting the virtual machine. This will take a while. Please wait.');

        $templateName = VirtualMachinesXenService::export($this->model, $this->repository);

        if(!Str::isUuid($templateName)) {
            $this->setFinishedWithError('We cannot export the virtual machine to the given repository.');
            Events::fire('conversion-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->setProgress(80, 'Creating the template records.');

        $image = RepositoryImagesService::create([
            'name'          =>  $this->model->name,
            'description'   =>  $this->model->description,
            'filename'      =>  $templateName . '.pvm',
            'path'          =>  $this->repository->vm_path . '/' . $templateName . '.pvm',
            'default_username'  =>  $this->model->username,
            'default_password'  =>  !is_null($this->model->password) ?? decrypt($this->model->password),
            'is_virtual_machine_image'     =>  true,
            'os'        =>  $this->model->os,
            'distro'    =>  $this->model->distro,
            'version'   =>  $this->model->version,
            'release_version'   =>  1,
            'supported_virtualizations'  => [
                $computeMember->hypervisor_model
            ],
            'iaas_repository_id'    =>  $this->repository->id,
            'cpu'   =>  $this->model->cpu,
            'ram'   =>  $this->model->ram,
            'iaas_virtual_machine_id'   =>  $this->model->id,
        ]);

        $this->setProgress(90, 'Unmounting the repository.');

        ComputeMemberXenService::unmountVmRepository($computeMember, $this->repository);

        $this->setProgress(100, 'Virtual machine exported');
    }
}
