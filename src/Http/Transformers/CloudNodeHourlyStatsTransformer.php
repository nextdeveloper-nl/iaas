<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\CloudNodeHourlyStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractCloudNodeHourlyStatsTransformer;

/**
 * Class CloudNodeHourlyStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class CloudNodeHourlyStatsTransformer extends AbstractCloudNodeHourlyStatsTransformer
{

    /**
     * @param CloudNodeHourlyStats $model
     *
     * @return array
     */
    public function transform(CloudNodeHourlyStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('CloudNodeHourlyStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('CloudNodeHourlyStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
