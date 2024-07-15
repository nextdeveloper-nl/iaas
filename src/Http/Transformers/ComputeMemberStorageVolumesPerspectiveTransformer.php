<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberStorageVolumesPerspectiveTransformer;

/**
 * Class ComputeMemberStorageVolumesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberStorageVolumesPerspectiveTransformer extends AbstractComputeMemberStorageVolumesPerspectiveTransformer
{

    /**
     * @param ComputeMemberStorageVolumesPerspective $model
     *
     * @return array
     */
    public function transform(ComputeMemberStorageVolumesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberStorageVolumesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberStorageVolumesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
