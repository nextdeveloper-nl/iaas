<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\EnvVarGroups;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractEnvVarGroupsTransformer;

/**
 * Class EnvVarGroupsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class EnvVarGroupsTransformer extends AbstractEnvVarGroupsTransformer
{

    /**
     * @param EnvVarGroups $model
     *
     * @return array
     */
    public function transform(EnvVarGroups $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('EnvVarGroups', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('EnvVarGroups', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
