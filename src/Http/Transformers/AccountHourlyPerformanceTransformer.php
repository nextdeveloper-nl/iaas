<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AccountHourlyPerformance;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAccountHourlyPerformanceTransformer;

/**
 * Class AccountHourlyPerformanceTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AccountHourlyPerformanceTransformer extends AbstractAccountHourlyPerformanceTransformer
{

    /**
     * @param AccountHourlyPerformance $model
     *
     * @return array
     */
    public function transform(AccountHourlyPerformance $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AccountHourlyPerformance', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AccountHourlyPerformance', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
