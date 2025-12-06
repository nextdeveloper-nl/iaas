<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\HealthChecks;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractHealthChecksTransformer;

/**
 * Class HealthChecksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class HealthChecksTransformer extends AbstractHealthChecksTransformer
{

    /**
     * @param HealthChecks $model
     *
     * @return array
     */
    public function transform(HealthChecks $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('HealthChecks', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('HealthChecks', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
