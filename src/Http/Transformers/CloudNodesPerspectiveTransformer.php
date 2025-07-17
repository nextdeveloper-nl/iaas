<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\CloudNodesPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractCloudNodesPerspectiveTransformer;

/**
 * Class CloudNodesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class CloudNodesPerspectiveTransformer extends AbstractCloudNodesPerspectiveTransformer
{

    /**
     * @param CloudNodesPerspective $model
     *
     * @return array
     */
    public function transform(CloudNodesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('CloudNodesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('CloudNodesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
