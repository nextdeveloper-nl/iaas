<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesManagementPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachinesManagementPerspectiveTransformer;

/**
 * Class VirtualMachinesManagementPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachinesManagementPerspectiveTransformer extends AbstractVirtualMachinesManagementPerspectiveTransformer
{

    /**
     * @param VirtualMachinesManagementPerspective $model
     *
     * @return array
     */
    public function transform(VirtualMachinesManagementPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachinesManagementPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachinesManagementPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
