<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmBackupStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmBackupStatsTransformer;

/**
 * Class VmBackupStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmBackupStatsTransformer extends AbstractVmBackupStatsTransformer
{

    /**
     * @param VmBackupStats $model
     *
     * @return array
     */
    public function transform(VmBackupStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmBackupStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmBackupStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
