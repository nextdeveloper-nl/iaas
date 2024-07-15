<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMembersPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMembersPerspectiveTransformer;

/**
 * Class ComputeMembersPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMembersPerspectiveTransformer extends AbstractComputeMembersPerspectiveTransformer
{

    /**
     * @param ComputeMembersPerspective $model
     *
     * @return array
     */
    public function transform(ComputeMembersPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMembersPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMembersPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
