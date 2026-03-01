<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmapStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmBackupHeatmapStatsTransformer;

/**
 * Class VmBackupHeatmapStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmBackupHeatmapStatsTransformer extends AbstractVmBackupHeatmapStatsTransformer
{

    /**
     * @param VmBackupHeatmapStats $model
     *
     * @return array
     */
    public function transform(VmBackupHeatmapStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmBackupHeatmapStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmBackupHeatmapStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
