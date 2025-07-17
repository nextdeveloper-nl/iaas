<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputePoolsPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputePoolsPerspectiveTransformer;

/**
 * Class ComputePoolsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputePoolsPerspectiveTransformer extends AbstractComputePoolsPerspectiveTransformer
{

    /**
     * @param ComputePoolsPerspective $model
     *
     * @return array
     */
    public function transform(ComputePoolsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputePoolsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputePoolsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
