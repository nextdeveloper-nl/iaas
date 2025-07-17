<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineBackupsTransformer;

/**
 * Class VirtualMachineBackupsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineBackupsTransformer extends AbstractVirtualMachineBackupsTransformer
{

    /**
     * @param VirtualMachineBackups $model
     *
     * @return array
     */
    public function transform(VirtualMachineBackups $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineBackups', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineBackups', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
