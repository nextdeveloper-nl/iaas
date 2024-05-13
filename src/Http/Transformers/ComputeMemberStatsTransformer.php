<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberStatsTransformer;

/**
 * Class ComputeMemberStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberStatsTransformer extends AbstractComputeMemberStatsTransformer
{

    /**
     * @param ComputeMemberStats $model
     *
     * @return array
     */
    public function transform(ComputeMemberStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
