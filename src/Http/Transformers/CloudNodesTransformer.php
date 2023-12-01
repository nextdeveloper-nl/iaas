<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractCloudNodesTransformer;

/**
 * Class CloudNodesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class CloudNodesTransformer extends AbstractCloudNodesTransformer
{

    /**
     * @param CloudNodes $model
     *
     * @return array
     */
    public function transform(CloudNodes $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('CloudNodes', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('CloudNodes', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
