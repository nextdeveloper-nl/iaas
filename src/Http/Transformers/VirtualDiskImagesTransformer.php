<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualDiskImagesTransformer;

/**
 * Class VirtualDiskImagesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualDiskImagesTransformer extends AbstractVirtualDiskImagesTransformer
{

    /**
     * @param VirtualDiskImages $model
     *
     * @return array
     */
    public function transform(VirtualDiskImages $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualDiskImages', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualDiskImages', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
