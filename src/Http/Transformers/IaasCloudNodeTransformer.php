<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IaasCloudNode;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIaasCloudNodeTransformer;

/**
 * Class IaasCloudNodeTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IaasCloudNodeTransformer extends AbstractIaasCloudNodeTransformer {

    /**
     * @param IaasCloudNode $model
     *
     * @return array
     */
    public function transform(IaasCloudNode $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('IaasCloudNode', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IaasCloudNode', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
