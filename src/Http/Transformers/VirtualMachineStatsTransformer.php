<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineStats;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineStatsTransformer;

/**
 * Class VirtualMachineStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineStatsTransformer extends AbstractVirtualMachineStatsTransformer
{

    /**
     * @param VirtualMachineStats $model
     *
     * @return array
     */
    public function transform(VirtualMachineStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
