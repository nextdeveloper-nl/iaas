<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\sStoragePools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractsStoragePoolsTransformer;

/**
 * Class sStoragePoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class sStoragePoolsTransformer extends AbstractsStoragePoolsTransformer {

    /**
     * @param sStoragePools $model
     *
     * @return array
     */
    public function transform(sStoragePools $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('sStoragePools', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('sStoragePools', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
