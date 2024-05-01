<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\StorageMemberDevices;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractStorageMemberDevicesTransformer;

/**
 * Class StorageMemberDevicesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class StorageMemberDevicesTransformer extends AbstractStorageMemberDevicesTransformer
{

    /**
     * @param StorageMemberDevices $model
     *
     * @return array
     */
    public function transform(StorageMemberDevices $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('StorageMemberDevices', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('StorageMemberDevices', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
