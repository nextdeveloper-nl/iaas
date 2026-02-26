<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AccountCurrentStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAccountCurrentStatsTransformer;

/**
 * Class AccountCurrentStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AccountCurrentStatsTransformer extends AbstractAccountCurrentStatsTransformer
{

    /**
     * @param AccountCurrentStats $model
     *
     * @return array
     */
    public function transform(AccountCurrentStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AccountCurrentStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AccountCurrentStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
