<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractBackupJobsTransformer;

/**
 * Class BackupJobsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class BackupJobsTransformer extends AbstractBackupJobsTransformer
{

    /**
     * @param BackupJobs $model
     *
     * @return array
     */
    public function transform(BackupJobs $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('BackupJobs', $model->uuid, 'Transformed')
        );

        if($transformed) {
            //return $transformed;
        }

        $transformed = parent::transform($model);

        $object = $model->object_type;

        $object = app($object)->where('id', $model->object_id)->first();

        $transformed['object_type'] = Str::replace('\Database\Models', '', get_class($object));
        $transformed['object_id'] = $object->uuid;

        Cache::set(
            CacheHelper::getKey('BackupJobs', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
