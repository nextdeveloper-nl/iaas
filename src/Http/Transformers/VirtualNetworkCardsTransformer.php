<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualNetworkCardsTransformer;

/**
 * Class VirtualNetworkCardsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualNetworkCardsTransformer extends AbstractVirtualNetworkCardsTransformer
{

    /**
     * @param VirtualNetworkCards $model
     *
     * @return array
     */
    public function transform(VirtualNetworkCards $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualNetworkCards', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualNetworkCards', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
