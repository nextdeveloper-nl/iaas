<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMemberTasks;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMemberTasksTransformer;

/**
 * Class ComputeMemberTasksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMemberTasksTransformer extends AbstractComputeMemberTasksTransformer
{

    /**
     * @param ComputeMemberTasks $model
     *
     * @return array
     */
    public function transform(ComputeMemberTasks $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMemberTasks', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMemberTasks', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
