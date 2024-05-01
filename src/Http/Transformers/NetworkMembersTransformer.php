<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkMembersTransformer;

/**
 * Class NetworkMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkMembersTransformer extends AbstractNetworkMembersTransformer
{

    /**
     * @param NetworkMembers $model
     *
     * @return array
     */
    public function transform(NetworkMembers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkMembers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkMembers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
