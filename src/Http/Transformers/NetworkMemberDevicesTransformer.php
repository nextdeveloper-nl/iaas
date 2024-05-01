<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkMemberDevices;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkMemberDevicesTransformer;

/**
 * Class NetworkMemberDevicesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkMemberDevicesTransformer extends AbstractNetworkMemberDevicesTransformer
{

    /**
     * @param NetworkMemberDevices $model
     *
     * @return array
     */
    public function transform(NetworkMemberDevices $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkMemberDevices', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkMemberDevices', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
