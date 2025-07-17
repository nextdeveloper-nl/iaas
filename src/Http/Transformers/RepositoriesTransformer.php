<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractRepositoriesTransformer;

/**
 * Class RepositoriesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class RepositoriesTransformer extends AbstractRepositoriesTransformer
{

    /**
     * @param Repositories $model
     *
     * @return array
     */
    public function transform(Repositories $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('Repositories', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('Repositories', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
