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
        $transformed = parent::transform($model);

        return $transformed;
    }
}
