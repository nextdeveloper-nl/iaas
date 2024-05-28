<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkPoolsTransformer;

/**
 * Class NetworkPoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkPoolsTransformer extends AbstractNetworkPoolsTransformer
{

    /**
     * @param NetworkPools $model
     *
     * @return array
     */
    public function transform(NetworkPools $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkPools', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkPools', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
