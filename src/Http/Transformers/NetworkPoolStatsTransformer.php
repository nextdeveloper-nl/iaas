<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkPoolStats;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkPoolStatsTransformer;

/**
 * Class NetworkPoolStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkPoolStatsTransformer extends AbstractNetworkPoolStatsTransformer
{

    /**
     * @param NetworkPoolStats $model
     *
     * @return array
     */
    public function transform(NetworkPoolStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkPoolStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkPoolStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
