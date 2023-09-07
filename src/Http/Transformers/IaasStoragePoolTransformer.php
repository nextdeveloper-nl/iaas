<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IaasStoragePool;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIaasStoragePoolTransformer;

/**
 * Class IaasStoragePoolTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IaasStoragePoolTransformer extends AbstractIaasStoragePoolTransformer {

    /**
     * @param IaasStoragePool $model
     *
     * @return array
     */
    public function transform(IaasStoragePool $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('IaasStoragePool', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IaasStoragePool', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
