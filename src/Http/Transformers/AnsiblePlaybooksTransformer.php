<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsiblePlaybooks;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsiblePlaybooksTransformer;

/**
 * Class AnsiblePlaybooksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsiblePlaybooksTransformer extends AbstractAnsiblePlaybooksTransformer
{

    /**
     * @param AnsiblePlaybooks $model
     *
     * @return array
     */
    public function transform(AnsiblePlaybooks $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsiblePlaybooks', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsiblePlaybooks', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
