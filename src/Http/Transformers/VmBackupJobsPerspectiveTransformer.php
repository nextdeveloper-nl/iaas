<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VmBackupJobsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVmBackupJobsPerspectiveTransformer;

/**
 * Class VmBackupJobsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VmBackupJobsPerspectiveTransformer extends AbstractVmBackupJobsPerspectiveTransformer
{

    /**
     * @param VmBackupJobsPerspective $model
     *
     * @return array
     */
    public function transform(VmBackupJobsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VmBackupJobsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VmBackupJobsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
