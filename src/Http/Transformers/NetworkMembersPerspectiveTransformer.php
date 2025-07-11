<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkMembersPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkMembersPerspectiveTransformer;

/**
 * Class NetworkMembersPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkMembersPerspectiveTransformer extends AbstractNetworkMembersPerspectiveTransformer
{

    /**
     * @param NetworkMembersPerspective $model
     *
     * @return array
     */
    public function transform(NetworkMembersPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkMembersPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkMembersPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
