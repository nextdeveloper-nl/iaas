<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StorageMembersPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStorageMembersPerspectiveTransformer;

/**
 * Class StorageMembersPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StorageMembersPerspectiveTransformer extends AbstractStorageMembersPerspectiveTransformer
{

    /**
     * @param StorageMembersPerspective $model
     *
     * @return array
     */
    public function transform(StorageMembersPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StorageMembersPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StorageMembersPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
