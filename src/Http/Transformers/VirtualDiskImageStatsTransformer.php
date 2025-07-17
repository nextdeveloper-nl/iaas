<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImageStats;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualDiskImageStatsTransformer;

/**
 * Class VirtualDiskImageStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualDiskImageStatsTransformer extends AbstractVirtualDiskImageStatsTransformer
{

    /**
     * @param VirtualDiskImageStats $model
     *
     * @return array
     */
    public function transform(VirtualDiskImageStats $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualDiskImageStats', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualDiskImageStats', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
