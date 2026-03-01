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
        $transformed = parent::transform($model);

        return $transformed;
    }
}
