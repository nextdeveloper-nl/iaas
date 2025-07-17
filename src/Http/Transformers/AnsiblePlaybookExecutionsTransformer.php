<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsiblePlaybookExecutions;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsiblePlaybookExecutionsTransformer;

/**
 * Class AnsiblePlaybookExecutionsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsiblePlaybookExecutionsTransformer extends AbstractAnsiblePlaybookExecutionsTransformer
{

    /**
     * @param AnsiblePlaybookExecutions $model
     *
     * @return array
     */
    public function transform(AnsiblePlaybookExecutions $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsiblePlaybookExecutions', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsiblePlaybookExecutions', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
