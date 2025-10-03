<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineCpuMetricsAggs;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineCpuMetricsAggsTransformer;

/**
 * Class VirtualMachineCpuMetricsAggsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineCpuMetricsAggsTransformer extends AbstractVirtualMachineCpuMetricsAggsTransformer
{

    /**
     * @param VirtualMachineCpuMetricsAggs $model
     *
     * @return array
     */
    public function transform(VirtualMachineCpuMetricsAggs $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineCpuMetricsAggs', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineCpuMetricsAggs', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
