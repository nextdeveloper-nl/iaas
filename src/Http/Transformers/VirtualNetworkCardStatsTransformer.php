<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCardStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualNetworkCardStatsTransformer;

/**
 * Class VirtualNetworkCardStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualNetworkCardStatsTransformer extends AbstractVirtualNetworkCardStatsTransformer
{

    /**
     * @param VirtualNetworkCardStats $model
     *
     * @return array
     */
    public function transform(VirtualNetworkCardStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualNetworkCardStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualNetworkCardStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
