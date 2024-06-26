<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\RepositoryImagesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractRepositoryImagesPerspectiveTransformer;

/**
 * Class RepositoryImagesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class RepositoryImagesPerspectiveTransformer extends AbstractRepositoryImagesPerspectiveTransformer
{

    /**
     * @param RepositoryImagesPerspective $model
     *
     * @return array
     */
    public function transform(RepositoryImagesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('RepositoryImagesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('RepositoryImagesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
