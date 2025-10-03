<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\BackupRetentionPolicies;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractBackupRetentionPoliciesTransformer;

/**
 * Class BackupRetentionPoliciesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class BackupRetentionPoliciesTransformer extends AbstractBackupRetentionPoliciesTransformer
{

    /**
     * @param BackupRetentionPolicies $model
     *
     * @return array
     */
    public function transform(BackupRetentionPolicies $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('BackupRetentionPolicies', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('BackupRetentionPolicies', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
