<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AccountHourlyStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAccountHourlyStatsTransformer;

/**
 * Class AccountHourlyStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AccountHourlyStatsTransformer extends AbstractAccountHourlyStatsTransformer
{

    /**
     * @param AccountHourlyStats $model
     *
     * @return array
     */
    public function transform(AccountHourlyStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AccountHourlyStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AccountHourlyStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
