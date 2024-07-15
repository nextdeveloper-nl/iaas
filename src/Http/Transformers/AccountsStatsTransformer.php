<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AccountsStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAccountsStatsTransformer;

/**
 * Class AccountsStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AccountsStatsTransformer extends AbstractAccountsStatsTransformer
{

    /**
     * @param AccountsStats $model
     *
     * @return array
     */
    public function transform(AccountsStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AccountsStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AccountsStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
