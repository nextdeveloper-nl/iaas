<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookAnsibleRoles;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsiblePlaybookAnsibleRolesTransformer;

/**
 * Class AnsiblePlaybookAnsibleRolesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsiblePlaybookAnsibleRolesTransformer extends AbstractAnsiblePlaybookAnsibleRolesTransformer
{

    /**
     * @param AnsiblePlaybookAnsibleRoles $model
     *
     * @return array
     */
    public function transform(AnsiblePlaybookAnsibleRoles $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsiblePlaybookAnsibleRoles', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsiblePlaybookAnsibleRoles', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
