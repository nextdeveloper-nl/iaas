<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputePoolsTransformer;

/**
 * Class ComputePoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputePoolsTransformer extends AbstractComputePoolsTransformer
{

    /**
     * @param ComputePools $model
     *
     * @return array
     */
    public function transform(ComputePools $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputePools', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputePools', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
