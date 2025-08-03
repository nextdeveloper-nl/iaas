<?php

namespace NextDeveloper\IAAS\Http\Controllers\VirtualMachines;

use App\Helpers\Http\ResponseHelper;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Requests\VirtualMachines\VirtualMachinesUpdateRequest;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\Commons\Http\Traits\Tags;
use NextDeveloper\Commons\Http\Traits\Addresses;

class VirtualMachinesMetricsController extends AbstractController
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
    public function index($uuid)
    {
        $vm = VirtualMachines::where('uuid', $uuid)->first();

        $availableMetrics = VirtualMachinesService::getAvailableMetrics($vm);

        if(!$console) {
            return ResponseHelper::error('Virtual machine console is not available at the moment. Please make sure that virtual machine is running. Otherwise please create a support ticket.');
        }

        return ResponsableFactory::makeResponse($this, $console);
    }
}
