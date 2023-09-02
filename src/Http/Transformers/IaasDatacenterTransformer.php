<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IaasDatacenter;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIaasDatacenterTransformer;

/**
 * Class IaasDatacenterTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IaasDatacenterTransformer extends AbstractIaasDatacenterTransformer {

    /**
     * @param IaasDatacenter $model
     *
     * @return array
     */
    public function transform(IaasDatacenter $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('IaasDatacenter', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IaasDatacenter', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
