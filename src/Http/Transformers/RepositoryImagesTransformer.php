<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractRepositoryImagesTransformer;

/**
 * Class RepositoryImagesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class RepositoryImagesTransformer extends AbstractRepositoryImagesTransformer
{

    /**
     * @param RepositoryImages $model
     *
     * @return array
     */
    public function transform(RepositoryImages $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('RepositoryImages', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('RepositoryImages', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
