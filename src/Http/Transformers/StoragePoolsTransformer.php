<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStoragePoolsTransformer;

/**
 * Class StoragePoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StoragePoolsTransformer extends AbstractStoragePoolsTransformer
{

    /**
     * @param StoragePools $model
     *
     * @return array
     */
    public function transform(StoragePools $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StoragePools', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StoragePools', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
