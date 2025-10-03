<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\MonitoringInstances;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractMonitoringInstancesTransformer;

/**
 * Class MonitoringInstancesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class MonitoringInstancesTransformer extends AbstractMonitoringInstancesTransformer
{

    /**
     * @param MonitoringInstances $model
     *
     * @return array
     */
    public function transform(MonitoringInstances $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('MonitoringInstances', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('MonitoringInstances', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
