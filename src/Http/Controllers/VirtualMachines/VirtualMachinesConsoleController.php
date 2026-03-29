<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachines;

use App\Helpers\Http\ResponseHelper;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\Commons\Http\Traits\Tags;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Http\Requests\VirtualMachines\VirtualMachinesUpdateRequest;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

class VirtualMachinesConsoleController extends AbstractController
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
    public function getConsoleData($virtualMachinesId, VirtualMachinesUpdateRequest $request)
    {
        $console = VirtualMachinesService::getConsoleDataFromVmId($virtualMachinesId);

        if(!$console) {
            return ResponseHelper::error('Virtual machine console is not available at the moment. Please make sure that virtual machine is running. Otherwise please create a support ticket.');
        }

        return ResponsableFactory::makeResponse($this, $console);
    }

    /**
     * Returns console connection data using a fresh XenAPI session token.
     * This method is intended for XenServer 8.2+ and the /xenserver82 nginx endpoint.
     *
     * @param  $virtualMachinesId
     * @param  VirtualMachinesUpdateRequest $request
     * @return mixed|null
     */
    public function getConsoleDataWithSessionRef($virtualMachinesId, VirtualMachinesUpdateRequest $request)
    {
        $vm = VirtualMachines::where('uuid', $virtualMachinesId)->first();

        if (!$vm) {
            return ResponseHelper::error('Virtual machine not found.');
        }

        $console = VirtualMachinesService::getConsoleDataWithSessionRef($vm);

        if (!$console) {
            return ResponseHelper::error('Virtual machine console is not available at the moment. Please make sure that virtual machine is running. Otherwise please create a support ticket.');
        }

        return ResponsableFactory::makeResponse($this, $console);
    }
}
