<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybooks;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsibleSystemPlaybooksTransformer;

/**
 * Class AnsibleSystemPlaybooksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsibleSystemPlaybooksTransformer extends AbstractAnsibleSystemPlaybooksTransformer
{

    /**
     * @param AnsibleSystemPlaybooks $model
     *
     * @return array
     */
    public function transform(AnsibleSystemPlaybooks $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsibleSystemPlaybooks', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsibleSystemPlaybooks', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
