<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmapByCloudStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmBackupHeatmapByCloudStatsTransformer;

/**
 * Class VmBackupHeatmapByCloudStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmBackupHeatmapByCloudStatsTransformer extends AbstractVmBackupHeatmapByCloudStatsTransformer
{

    /**
     * @param VmBackupHeatmapByCloudStats $model
     *
     * @return array
     */
    public function transform(VmBackupHeatmapByCloudStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmBackupHeatmapByCloudStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmBackupHeatmapByCloudStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
