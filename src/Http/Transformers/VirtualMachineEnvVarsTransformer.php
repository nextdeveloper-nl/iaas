<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineEnvVars;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineEnvVarsTransformer;

/**
 * Class VirtualMachineEnvVarsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineEnvVarsTransformer extends AbstractVirtualMachineEnvVarsTransformer
{

    /**
     * @param VirtualMachineEnvVars $model
     *
     * @return array
     */
    public function transform(VirtualMachineEnvVars $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineEnvVars', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineEnvVars', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
