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
}
