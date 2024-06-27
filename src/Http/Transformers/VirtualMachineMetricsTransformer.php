<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMetrics;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineMetricsTransformer;

/**
 * Class VirtualMachineMetricsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineMetricsTransformer extends AbstractVirtualMachineMetricsTransformer
{

    /**
     * @param VirtualMachineMetrics $model
     *
     * @return array
     */
    public function transform(VirtualMachineMetrics $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineMetrics', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineMetrics', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
