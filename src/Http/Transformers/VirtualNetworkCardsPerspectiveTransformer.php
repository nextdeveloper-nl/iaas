<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCardsPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualNetworkCardsPerspectiveTransformer;

/**
 * Class VirtualNetworkCardsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualNetworkCardsPerspectiveTransformer extends AbstractVirtualNetworkCardsPerspectiveTransformer
{

    /**
     * @param VirtualNetworkCardsPerspective $model
     *
     * @return array
     */
    public function transform(VirtualNetworkCardsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualNetworkCardsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualNetworkCardsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
