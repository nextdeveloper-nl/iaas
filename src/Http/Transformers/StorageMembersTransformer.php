<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StorageMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStorageMembersTransformer;

/**
 * Class StorageMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StorageMembersTransformer extends AbstractStorageMembersTransformer
{

    /**
     * @param StorageMembers $model
     *
     * @return array
     */
    public function transform(StorageMembers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StorageMembers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StorageMembers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
