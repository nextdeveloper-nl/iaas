<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractGatewaysTransformer;

/**
 * Class GatewaysTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class GatewaysTransformer extends AbstractGatewaysTransformer
{

    /**
     * @param Gateways $model
     *
     * @return array
     */
    public function transform(Gateways $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Gateways', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Gateways', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
