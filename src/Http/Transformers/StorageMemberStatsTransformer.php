<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StorageMemberStats;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStorageMemberStatsTransformer;

/**
 * Class StorageMemberStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StorageMemberStatsTransformer extends AbstractStorageMemberStatsTransformer
{

    /**
     * @param StorageMemberStats $model
     *
     * @return array
     */
    public function transform(StorageMemberStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StorageMemberStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StorageMemberStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
