<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\sComputePools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractsComputePoolsTransformer;

/**
 * Class sComputePoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class sComputePoolsTransformer extends AbstractsComputePoolsTransformer {

    /**
     * @param sComputePools $model
     *
     * @return array
     */
    public function transform(sComputePools $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('sComputePools', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('sComputePools', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
