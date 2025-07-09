<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkStats;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkStatsTransformer;

/**
 * Class NetworkStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkStatsTransformer extends AbstractNetworkStatsTransformer
{

    /**
     * @param NetworkStats $model
     *
     * @return array
     */
    public function transform(NetworkStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
