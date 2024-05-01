<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberStorageVolumesTransformer;

/**
 * Class ComputeMemberStorageVolumesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberStorageVolumesTransformer extends AbstractComputeMemberStorageVolumesTransformer
{

    /**
     * @param ComputeMemberStorageVolumes $model
     *
     * @return array
     */
    public function transform(ComputeMemberStorageVolumes $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberStorageVolumes', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberStorageVolumes', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
