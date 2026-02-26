<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\CloudNodesPerformance;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractCloudNodesPerformanceTransformer;

/**
 * Class CloudNodesPerformanceTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class CloudNodesPerformanceTransformer extends AbstractCloudNodesPerformanceTransformer
{

    /**
     * @param CloudNodesPerformance $model
     *
     * @return array
     */
    public function transform(CloudNodesPerformance $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('CloudNodesPerformance', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('CloudNodesPerformance', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
