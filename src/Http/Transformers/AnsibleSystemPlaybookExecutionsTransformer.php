<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybookExecutions;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsibleSystemPlaybookExecutionsTransformer;

/**
 * Class AnsibleSystemPlaybookExecutionsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsibleSystemPlaybookExecutionsTransformer extends AbstractAnsibleSystemPlaybookExecutionsTransformer
{

    /**
     * @param AnsibleSystemPlaybookExecutions $model
     *
     * @return array
     */
    public function transform(AnsibleSystemPlaybookExecutions $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsibleSystemPlaybookExecutions', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsibleSystemPlaybookExecutions', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
