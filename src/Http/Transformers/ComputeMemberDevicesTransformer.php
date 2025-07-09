<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberDevices;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberDevicesTransformer;

/**
 * Class ComputeMemberDevicesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberDevicesTransformer extends AbstractComputeMemberDevicesTransformer
{

    /**
     * @param ComputeMemberDevices $model
     *
     * @return array
     */
    public function transform(ComputeMemberDevices $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberDevices', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberDevices', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
