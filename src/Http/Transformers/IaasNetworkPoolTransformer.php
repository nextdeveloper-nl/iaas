<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IaasNetworkPool;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIaasNetworkPoolTransformer;

/**
 * Class IaasNetworkPoolTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IaasNetworkPoolTransformer extends AbstractIaasNetworkPoolTransformer {

    /**
     * @param IaasNetworkPool $model
     *
     * @return array
     */
    public function transform(IaasNetworkPool $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('IaasNetworkPool', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IaasNetworkPool', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
