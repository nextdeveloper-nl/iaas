<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ActiveAlarmsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractActiveAlarmsPerspectiveTransformer;

/**
 * Class ActiveAlarmsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ActiveAlarmsPerspectiveTransformer extends AbstractActiveAlarmsPerspectiveTransformer
{

    /**
     * @param ActiveAlarmsPerspective $model
     *
     * @return array
     */
    public function transform(ActiveAlarmsPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ActiveAlarmsPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ActiveAlarmsPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
