<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineCpuAlerts;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineCpuAlertsTransformer;

/**
 * Class VirtualMachineCpuAlertsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineCpuAlertsTransformer extends AbstractVirtualMachineCpuAlertsTransformer
{

    /**
     * @param VirtualMachineCpuAlerts $model
     *
     * @return array
     */
    public function transform(VirtualMachineCpuAlerts $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineCpuAlerts', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineCpuAlerts', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
