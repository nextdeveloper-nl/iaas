<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineEnvVarGroups;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineEnvVarGroupsTransformer;

/**
 * Class VirtualMachineEnvVarGroupsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineEnvVarGroupsTransformer extends AbstractVirtualMachineEnvVarGroupsTransformer
{

    /**
     * @param VirtualMachineEnvVarGroups $model
     *
     * @return array
     */
    public function transform(VirtualMachineEnvVarGroups $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineEnvVarGroups', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineEnvVarGroups', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
