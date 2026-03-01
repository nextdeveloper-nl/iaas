<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmaps;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmBackupHeatmapsTransformer;

/**
 * Class VmBackupHeatmapsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmBackupHeatmapsTransformer extends AbstractVmBackupHeatmapsTransformer
{

    /**
     * @param VmBackupHeatmaps $model
     *
     * @return array
     */
    public function transform(VmBackupHeatmaps $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmBackupHeatmaps', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmBackupHeatmaps', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
