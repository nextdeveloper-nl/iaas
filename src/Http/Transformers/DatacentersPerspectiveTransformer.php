<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\DatacentersPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractDatacentersPerspectiveTransformer;

/**
 * Class DatacentersPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class DatacentersPerspectiveTransformer extends AbstractDatacentersPerspectiveTransformer
{

    /**
     * @param DatacentersPerspective $model
     *
     * @return array
     */
    public function transform(DatacentersPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('DatacentersPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('DatacentersPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
