<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\RepositoriesPerspective;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractRepositoriesPerspectiveTransformer;

/**
 * Class RepositoriesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class RepositoriesPerspectiveTransformer extends AbstractRepositoriesPerspectiveTransformer
{

    /**
     * @param RepositoriesPerspective $model
     *
     * @return array
     */
    public function transform(RepositoriesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('RepositoriesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('RepositoriesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
