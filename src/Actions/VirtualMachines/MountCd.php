<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use GPBMetadata\Google\Api\Auth;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action unplugs the virtual machine, and then plugs it back in
 */
class MountCd extends AbstractAction
{
    public const EVENTS = [
        'mounting-cd:NextDeveloper\IAAS\VirtualMachines',
        'cd-mounted:NextDeveloper\IAAS\VirtualMachines',
        'mounting-cd-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public const PARAMS = [
        'iaas_repository_image_id'  =>  'required|exists:iaas_repository_images,uuid'
    ];

    private $repoImage;

    public function __construct(VirtualMachines $vm, $params)
    {
        if(array_key_exists(0, $params))
            $params = $params[0];

        $this->queue = 'iaas';

        $this->model = $vm;

        parent::__construct($params);

        $this->repoImage = RepositoryImages::where('uuid', $params['iaas_repository_image_id'])->first();
    }

    public function handle()
    {
        $this->setProgress(0, 'Mounting CD to virtual machine');

        Events::fire('mounting-cd:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(30, 'Mounting ISO library if its not mounted');

        //  In case the ISO library is not mounted, we are trying to mount it here
        $repo = Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->repoImage->iaas_repository_id)
            ->first();

        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->model->iaas_compute_member_id)
            ->first();

        $isMounted = ComputeMemberXenService::mountIsoRepository($computeMember, $repo);

        if(!$isMounted) {
            Events::fire('mounting-cd-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->setFinishedWithError('Mounting cd failed, because we cannot mount ISO library.');
            return;
        }

        $this->setProgress(60, 'Mounting CD');

        $result = VirtualMachinesXenService::mountCD($this->model, $this->repoImage);

        if(!$result) {
            Events::fire('mounting-cd-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);

            $this->setFinishedWithError('Mounting cd failed');
            return;
        }

        Events::fire('cd-mounted:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setFinished('CD Mounted');
    }
}
