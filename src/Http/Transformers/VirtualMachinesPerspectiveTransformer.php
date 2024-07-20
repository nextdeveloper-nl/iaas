<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachinesPerspectiveTransformer;

/**
 * Class VirtualMachinesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachinesPerspectiveTransformer extends AbstractVirtualMachinesPerspectiveTransformer
{

    /**
     * @param VirtualMachinesPerspective $model
     *
     * @return array
     */
    public function transform(VirtualMachinesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachinesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachinesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
