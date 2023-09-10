<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStorageVolumesTransformer;

/**
 * Class StorageVolumesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StorageVolumesTransformer extends AbstractStorageVolumesTransformer {

    /**
     * @param StorageVolumes $model
     *
     * @return array
     */
    public function transform(StorageVolumes $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('StorageVolumes', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StorageVolumes', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
