<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IaasComputePool;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIaasComputePoolTransformer;

/**
 * Class IaasComputePoolTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IaasComputePoolTransformer extends AbstractIaasComputePoolTransformer {

    /**
     * @param IaasComputePool $model
     *
     * @return array
     */
    public function transform(IaasComputePool $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('IaasComputePool', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IaasComputePool', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
