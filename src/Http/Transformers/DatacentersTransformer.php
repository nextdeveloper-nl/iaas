<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractDatacentersTransformer;

/**
 * Class DatacentersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class DatacentersTransformer extends AbstractDatacentersTransformer
{

    /**
     * @param Datacenters $model
     *
     * @return array
     */
    public function transform(Datacenters $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Datacenters', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Datacenters', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
