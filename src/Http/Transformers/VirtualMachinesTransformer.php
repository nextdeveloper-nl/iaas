<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use League\Fractal\ParamBag;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachinesTransformer;


/**
 * Class VirtualMachinesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachinesTransformer extends AbstractVirtualMachinesTransformer
{
    public function __construct(ParamBag $paramBag = null)
    {
        $this->addInclude('virtualNetworkCards');

        parent::__construct($paramBag);
    }
    /**
     * @param VirtualMachines $model
     *
     * @return array
     */
    public function transform(VirtualMachines $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachines', $model->uuid, 'Transformed')
        );

        if($transformed) {
            //return $transformed;
        }

        $transformed = parent::transform($model);

        $vm = VirtualMachines::where('id', $transformed['snapshot_of_virtual_machine'])->first();

        if($vm) $transformed['snapshot_of_virtual_machine'] = $vm->uuid;

        //  Surface the VM's selected service roles as their own key instead of making
        //  API consumers dig through the generic features blob (see features.service_roles).
        $transformed['service_roles'] = $model->features['service_roles'] ?? [];

        unset($transformed['hypervisor_uuid']);
        unset($transformed['hypervisor_data']);
        unset($transformed['console_data']);

        Cache::set(
            CacheHelper::getKey('VirtualMachines', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }

    public function includeVirtualNetworkCards(VirtualMachines $model)
    {

    }
}
