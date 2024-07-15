<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StorageVolumesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStorageVolumesPerspectiveTransformer;

/**
 * Class StorageVolumesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StorageVolumesPerspectiveTransformer extends AbstractStorageVolumesPerspectiveTransformer
{

    /**
     * @param StorageVolumesPerspective $model
     *
     * @return array
     */
    public function transform(StorageVolumesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StorageVolumesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StorageVolumesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
