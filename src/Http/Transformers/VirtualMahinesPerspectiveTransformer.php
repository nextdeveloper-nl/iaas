<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMahinesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMahinesPerspectiveTransformer;

/**
 * Class VirtualMahinesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMahinesPerspectiveTransformer extends AbstractVirtualMahinesPerspectiveTransformer
{

    /**
     * @param VirtualMahinesPerspective $model
     *
     * @return array
     */
    public function transform(VirtualMahinesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMahinesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMahinesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
