<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberMetrics;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberMetricsTransformer;

/**
 * Class ComputeMemberMetricsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberMetricsTransformer extends AbstractComputeMemberMetricsTransformer
{

    /**
     * @param ComputeMemberMetrics $model
     *
     * @return array
     */
    public function transform(ComputeMemberMetrics $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberMetrics', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberMetrics', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
