<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkMembersInterfacesTransformer;

/**
 * Class NetworkMembersInterfacesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkMembersInterfacesTransformer extends AbstractNetworkMembersInterfacesTransformer
{

    /**
     * @param NetworkMembersInterfaces $model
     *
     * @return array
     */
    public function transform(NetworkMembersInterfaces $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkMembersInterfaces', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkMembersInterfaces', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
