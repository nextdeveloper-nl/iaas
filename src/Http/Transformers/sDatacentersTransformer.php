<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\sDatacenters;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractsDatacentersTransformer;

/**
 * Class sDatacentersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class sDatacentersTransformer extends AbstractsDatacentersTransformer {

    /**
     * @param sDatacenters $model
     *
     * @return array
     */
    public function transform(sDatacenters $model) {
        $transformed = Cache::get(
            CacheHelper::getKey('sDatacenters', $model->uuid, 'Transformed')
        );

        if($transformed)
            return $transformed;

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('sDatacenters', $model->uuid, 'Transformed'),
            $transformed
        );

        return parent::transform($model);
    }
}
