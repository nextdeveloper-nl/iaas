<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberNetworkInterfacesTransformer;

/**
 * Class ComputeMemberNetworkInterfacesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberNetworkInterfacesTransformer extends AbstractComputeMemberNetworkInterfacesTransformer
{

    /**
     * @param ComputeMemberNetworkInterfaces $model
     *
     * @return array
     */
    public function transform(ComputeMemberNetworkInterfaces $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberNetworkInterfaces', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberNetworkInterfaces', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
