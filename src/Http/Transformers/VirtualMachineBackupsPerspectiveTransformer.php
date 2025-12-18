<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackupsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineBackupsPerspectiveTransformer;

/**
 * Class VirtualMachineBackupsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineBackupsPerspectiveTransformer extends AbstractVirtualMachineBackupsPerspectiveTransformer
{

    /**
     * @param VirtualMachineBackupsPerspective $model
     *
     * @return array
     */
    public function transform(VirtualMachineBackupsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineBackupsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineBackupsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
