<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmBackupPerAccountStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmBackupPerAccountStatsTransformer;

/**
 * Class VmBackupPerAccountStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmBackupPerAccountStatsTransformer extends AbstractVmBackupPerAccountStatsTransformer
{

    /**
     * @param VmBackupPerAccountStats $model
     *
     * @return array
     */
    public function transform(VmBackupPerAccountStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmBackupPerAccountStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmBackupPerAccountStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
