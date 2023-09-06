<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IaasComputeMember;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIaasComputeMemberTransformer;

/**
 * Class IaasComputeMemberTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IaasComputeMemberTransformer extends AbstractIaasComputeMemberTransformer {

    /**
     * @param IaasComputeMember $model
     *
     * @return array
     */
    public function transform(IaasComputeMember $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('IaasComputeMember', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IaasComputeMember', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
