<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmDailyStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmDailyStatsTransformer;

/**
 * Class VmDailyStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmDailyStatsTransformer extends AbstractVmDailyStatsTransformer
{

    /**
     * @param VmDailyStats $model
     *
     * @return array
     */
    public function transform(VmDailyStats $model)
    {
        $transformed = parent::transform($model);
        return $transformed;
    }
}
