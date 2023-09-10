<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\sComputeMembers;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractsComputeMembersTransformer;

/**
 * Class sComputeMembersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class sComputeMembersTransformer extends AbstractsComputeMembersTransformer {

    /**
     * @param sComputeMembers $model
     *
     * @return array
     */
    public function transform(sComputeMembers $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('sComputeMembers', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('sComputeMembers', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
