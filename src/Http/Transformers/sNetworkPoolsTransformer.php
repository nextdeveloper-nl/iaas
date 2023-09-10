<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\sNetworkPools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractsNetworkPoolsTransformer;

/**
 * Class sNetworkPoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class sNetworkPoolsTransformer extends AbstractsNetworkPoolsTransformer {

    /**
     * @param sNetworkPools $model
     *
     * @return array
     */
    public function transform(sNetworkPools $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('sNetworkPools', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('sNetworkPools', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
