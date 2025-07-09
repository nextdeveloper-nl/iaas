<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\Licences;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractLicencesTransformer;

/**
 * Class LicencesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class LicencesTransformer extends AbstractLicencesTransformer
{

    /**
     * @param Licences $model
     *
     * @return array
     */
    public function transform(Licences $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Licences', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Licences', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
