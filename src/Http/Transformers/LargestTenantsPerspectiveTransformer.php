<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\LargestTenantsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractLargestTenantsPerspectiveTransformer;

/**
 * Class LargestTenantsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class LargestTenantsPerspectiveTransformer extends AbstractLargestTenantsPerspectiveTransformer
{

    /**
     * @param LargestTenantsPerspective $model
     *
     * @return array
     */
    public function transform(LargestTenantsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('LargestTenantsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('LargestTenantsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
