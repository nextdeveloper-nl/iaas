<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsibleServers;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsibleServersTransformer;

/**
 * Class AnsibleServersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsibleServersTransformer extends AbstractAnsibleServersTransformer
{

    /**
     * @param AnsibleServers $model
     *
     * @return array
     */
    public function transform(AnsibleServers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsibleServers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsibleServers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
