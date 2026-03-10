<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\EnvVarGroupVars;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractEnvVarGroupVarsTransformer;

/**
 * Class EnvVarGroupVarsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class EnvVarGroupVarsTransformer extends AbstractEnvVarGroupVarsTransformer
{

    /**
     * @param EnvVarGroupVars $model
     *
     * @return array
     */
    public function transform(EnvVarGroupVars $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('EnvVarGroupVars', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('EnvVarGroupVars', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
