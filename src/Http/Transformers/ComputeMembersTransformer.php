<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractComputeMembersTransformer;

/**
 * Class ComputeMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class ComputeMembersTransformer extends AbstractComputeMembersTransformer
{

    /**
     * @param ComputeMembers $model
     *
     * @return array
     */
    public function transform(ComputeMembers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('ComputeMembers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('ComputeMembers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
