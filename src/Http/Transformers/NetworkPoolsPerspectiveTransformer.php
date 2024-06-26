<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\NetworkPoolsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworkPoolsPerspectiveTransformer;

/**
 * Class NetworkPoolsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworkPoolsPerspectiveTransformer extends AbstractNetworkPoolsPerspectiveTransformer
{

    /**
     * @param NetworkPoolsPerspective $model
     *
     * @return array
     */
    public function transform(NetworkPoolsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('NetworkPoolsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('NetworkPoolsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
