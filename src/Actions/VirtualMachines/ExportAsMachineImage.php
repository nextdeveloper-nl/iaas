<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Contracts\ExportCapableInterface;
use NextDeveloper\IAAS\Exceptions\CannotContinueException;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This action exports a halted virtual machine into a machine image repository and
 * registers the resulting export as a new RepositoryImages record, so it shows up in
 * the machine image repository list and can be used to launch new virtual machines.
 */
class ExportAsMachineImage extends AbstractAction
{
    public const EVENTS = [
        'exporting-as-machine-image:NextDeveloper\IAAS\VirtualMachines',
        'exported-as-machine-image:NextDeveloper\IAAS\VirtualMachines',
        'export-as-machine-image-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public const PARAMS = [
        'iaas_repository_id'    =>  'required|exists:iaas_repositories,uuid',
        'name'                  =>  'nullable|string',
        'is_public'             =>  'nullable|boolean',
    ];

    /**
     * @var Repositories
     */
    private $repository;

    /**
     * @var ComputeMembers
     */
    private $computeMember;

    public function __construct(VirtualMachines $vm, $params)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct($params);

        //  Using $this->params instead of the constructor argument because AbstractAction
        //  unwraps the "do" action request payload (it may arrive wrapped as $params[0]).
        $this->repository = Repositories::where('uuid', $this->params['iaas_repository_id'])->first();

        if($this->repository->is_public == false && $this->repository->iam_account_id != UserHelper::currentAccount()->id)
            throw new NotAllowedException('This repository is not public nor its yours. That is why' .
                ' you cannot export the virtual machine to this repository as a machine image.');

        if($this->model->status != 'halted')
            throw new CannotContinueException('You can only export a halted virtual machine as a machine' .
                ' image. Please shutdown the virtual machine before exporting it. Or you can take a' .
                ' snapshot, convert that into a proper virtual machine and then export that one instead.');

        $this->computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->model->iaas_compute_member_id)
            ->first();

        //  The repository is mounted on the compute member via NFS using its local_ip_addr,
        //  so it has to live on the same cloud node as the virtual machine we are exporting.
        $cloudNode = VirtualMachinesService::getCloudPool($this->model);

        if($this->repository->iaas_cloud_node_id != $cloudNode->id)
            throw new CannotContinueException('The given repository does not belong to the cloud node' .
                ' this virtual machine lives on. Please select a machine image repository on the same' .
                ' cloud node.');
    }

    public function handle()
    {
        $this->setProgress(0, 'Export of virtual machine as a machine image started');
        Events::fire('exporting-as-machine-image:NextDeveloper\IAAS\VirtualMachines', $this->model);

        if($this->model->is_lost) {
            $this->setFinishedWithError('Unfortunately this vm is lost, that is why we cannot continue.');
            Events::fire('export-as-machine-image-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinishedWithError('I cannot complete this process because the VM is already deleted');
            Events::fire('export-as-machine-image-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model = app(VirtualMachineManager::class)->sync($this->model);

        if(!$this->model->hypervisor_data || !array_key_exists('power-state', $this->model->hypervisor_data)) {
            //  The hypervisor did not return a usable power-state, so this VM's state
            //  cannot be trusted right now. HealthCheck (which used to investigate this
            //  further) has been retired - flag it for manual investigation instead of
            //  dispatching a no-op job.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            Events::fire('export-as-machine-image-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->setFinishedWithError('Could not determine the virtual machine\'s state after this operation. It has been marked for manual investigation.');

            return;
        }

        if($this->model->status != 'halted') {
            $this->setFinishedWithError('We cannot export the virtual machine. It is not halted.');
            Events::fire('export-as-machine-image-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->setProgress(20, 'Mounting the machine image repository on the compute member.');

        //  Not routed through VirtualMachineManager: repo mount/unmount has no capability
        //  interface yet - see docs/hypervisor-driver-architecture.md.
        $isMounted = ComputeMemberXenService::mountVmRepository($this->computeMember, $this->repository);

        if(!$isMounted) {
            $this->setFinishedWithError('We cannot mount the given repository, that is why we cannot' .
                ' export this virtual machine as a machine image.');
            Events::fire('export-as-machine-image-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->setProgress(50, 'Exporting the virtual machine. This will take a while. Please wait.');

        $driver = app(VirtualMachineManager::class)->getAdapter($this->model);
        $exportedUuid = $driver instanceof ExportCapableInterface
            ? $driver->exportToRepository($this->model, $this->repository)
            : null;

        $this->setProgress(80, 'Registering the machine image in the repository list.');

        //  This is what makes the export show up in the machine image repository list.
        $image = RepositoryImagesService::create([
            'name'                  =>  $this->params['name'] ?? $this->model->name,
            'description'           =>  $this->model->description,
            'filename'              =>  $exportedUuid . '.pvm',
            'path'                  =>  $this->repository->vm_path . '/' . $exportedUuid . '.pvm',
            'default_username'     =>  $this->model->username,
            'default_password'     =>  VirtualMachinesService::getRawPasswordById($this->model->id),
            'is_virtual_machine_image'  =>  true,
            'is_public'             =>  $this->params['is_public'] ?? false,
            'os'                    =>  $this->model->os,
            'distro'                =>  $this->model->distro,
            'version'               =>  $this->model->version,
            'release_version'       =>  1,
            'supported_virtualizations' => [
                $this->computeMember->hypervisor_model
            ],
            'iaas_repository_id'    =>  $this->repository->id,
            'cpu'                   =>  $this->model->cpu,
            'ram'                   =>  $this->model->ram,
            'iaas_virtual_machine_id'   =>  $this->model->id,
        ]);

        //  Computing the actual file size/hash of the exported image on disk.
        RepositoryImagesService::updateRepoSize($image);

        $this->setProgress(90, 'Unmounting the repository.');

        ComputeMemberXenService::unmountVmRepository($this->computeMember, $this->repository);

        $this->setStateData('repository_image', $image->fresh());

        Events::fire('exported-as-machine-image:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->setProgress(100, 'Virtual machine exported as a machine image and added to the' .
            ' machine image repository list.');
    }
}
