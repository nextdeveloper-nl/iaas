<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\HealthChecksPerformance;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractHealthChecksPerformanceTransformer;

/**
 * Class HealthChecksPerformanceTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class HealthChecksPerformanceTransformer extends AbstractHealthChecksPerformanceTransformer
{

    /**
     * @param HealthChecksPerformance $model
     *
     * @return array
     */
    public function transform(HealthChecksPerformance $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('HealthChecksPerformance', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('HealthChecksPerformance', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
