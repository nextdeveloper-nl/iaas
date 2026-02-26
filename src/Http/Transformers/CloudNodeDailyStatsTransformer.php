<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\CloudNodeDailyStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractCloudNodeDailyStatsTransformer;

/**
 * Class CloudNodeDailyStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class CloudNodeDailyStatsTransformer extends AbstractCloudNodeDailyStatsTransformer
{

    /**
     * @param CloudNodeDailyStats $model
     *
     * @return array
     */
    public function transform(CloudNodeDailyStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('CloudNodeDailyStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('CloudNodeDailyStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
