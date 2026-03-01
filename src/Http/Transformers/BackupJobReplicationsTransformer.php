<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\BackupJobReplications;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractBackupJobReplicationsTransformer;

/**
 * Class BackupJobReplicationsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class BackupJobReplicationsTransformer extends AbstractBackupJobReplicationsTransformer
{

    /**
     * @param BackupJobReplications $model
     *
     * @return array
     */
    public function transform(BackupJobReplications $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('BackupJobReplications', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('BackupJobReplications', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
