<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmHourlyStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmHourlyStatsTransformer;

/**
 * Class VmHourlyStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmHourlyStatsTransformer extends AbstractVmHourlyStatsTransformer
{

    /**
     * @param VmHourlyStats $model
     *
     * @return array
     */
    public function transform(VmHourlyStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmHourlyStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmHourlyStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
