<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlays;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractAnsibleSystemPlaysTransformer;

/**
 * Class AnsibleSystemPlaysTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AnsibleSystemPlaysTransformer extends AbstractAnsibleSystemPlaysTransformer
{

    /**
     * @param AnsibleSystemPlays $model
     *
     * @return array
     */
    public function transform(AnsibleSystemPlays $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('AnsibleSystemPlays', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('AnsibleSystemPlays', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
