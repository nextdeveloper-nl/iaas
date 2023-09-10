<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\sCloudNodes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractsCloudNodesTransformer;

/**
 * Class sCloudNodesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class sCloudNodesTransformer extends AbstractsCloudNodesTransformer {

    /**
     * @param sCloudNodes $model
     *
     * @return array
     */
    public function transform(sCloudNodes $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('sCloudNodes', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('sCloudNodes', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
