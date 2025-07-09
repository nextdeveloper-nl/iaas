<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworksPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworksPerspectiveTransformer;

/**
 * Class NetworksPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworksPerspectiveTransformer extends AbstractNetworksPerspectiveTransformer
{

    /**
     * @param NetworksPerspective $model
     *
     * @return array
     */
    public function transform(NetworksPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworksPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworksPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
