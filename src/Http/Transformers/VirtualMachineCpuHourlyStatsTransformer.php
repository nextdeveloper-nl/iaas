<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineCpuHourlyStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineCpuHourlyStatsTransformer;

/**
 * Class VirtualMachineCpuHourlyStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineCpuHourlyStatsTransformer extends AbstractVirtualMachineCpuHourlyStatsTransformer
{

    /**
     * @param VirtualMachineCpuHourlyStats $model
     *
     * @return array
     */
    public function transform(VirtualMachineCpuHourlyStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineCpuHourlyStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineCpuHourlyStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
