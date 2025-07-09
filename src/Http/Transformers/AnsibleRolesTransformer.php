<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsibleRoles;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsibleRolesTransformer;

/**
 * Class AnsibleRolesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsibleRolesTransformer extends AbstractAnsibleRolesTransformer
{

    /**
     * @param AnsibleRoles $model
     *
     * @return array
     */
    public function transform(AnsibleRoles $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsibleRoles', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsibleRoles', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
