<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractNetworksTransformer;

/**
 * Class NetworksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class NetworksTransformer extends AbstractNetworksTransformer
{

    /**
     * @param Networks $model
     *
     * @return array
     */
    public function transform(Networks $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Networks', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Networks', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
