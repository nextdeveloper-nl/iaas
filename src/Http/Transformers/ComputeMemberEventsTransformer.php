<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberEventsTransformer;

/**
 * Class ComputeMemberEventsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberEventsTransformer extends AbstractComputeMemberEventsTransformer
{

    /**
     * @param ComputeMemberEvents $model
     *
     * @return array
     */
    public function transform(ComputeMemberEvents $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberEvents', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberEvents', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
