<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachines;

use App\Helpers\Http\ResponseHelper;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachines\VirtualMachinesUpdateRequest;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\VirtualMachinesMetadataService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\Commons\Http\Traits\Tags;
use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\IAM\Helpers\UserHelper;

class VirtualMachinesMetadataController extends AbstractController
{
    private $model = VirtualMachines::class;

    use Tags;
    use Addresses;

    /**
     * This method updates VirtualMachines object on database.
     *
     * @param  $virtualMachinesId
     * @param  VirtualMachinesUpdateRequest $request
     * @return mixed|null
     * @throws \NextDeveloper\Commons\Exceptions\CannotCreateModelException
     */
    public function getMetadata($uuid)
    {
        $vm = VirtualMachinesService::getVirtualMachineByHypervisorUuid($uuid);

        UserHelper::setUserById($vm->iam_user_id);
        UserHelper::setCurrentAccountById($vm->iam_account_id);

        return VirtualMachinesMetadataService::getMetadata($vm);
    }

    public function getCloudInitConfiguration($uuid)
    {
        $vm = VirtualMachinesService::getVirtualMachineByHypervisorUuid($uuid);

        UserHelper::setUserById($vm->iam_user_id);
        UserHelper::setCurrentAccountById($vm->iam_account_id);

        return VirtualMachinesMetadataService::getCloudInitConfiguration($vm);
    }
}
