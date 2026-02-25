<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\KpiPerformance;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractKpiPerformanceTransformer;

/**
 * Class KpiPerformanceTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class KpiPerformanceTransformer extends AbstractKpiPerformanceTransformer
{

    /**
     * @param KpiPerformance $model
     *
     * @return array
     */
    public function transform(KpiPerformance $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('KpiPerformance', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('KpiPerformance', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
