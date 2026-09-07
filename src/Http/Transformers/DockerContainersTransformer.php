<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\DockerContainers;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractDockerContainersTransformer;

/**
 * Class DockerContainersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class DockerContainersTransformer extends AbstractDockerContainersTransformer
{

    /**
     * @param DockerContainers $model
     *
     * @return array
     */
    public function transform(DockerContainers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('DockerContainers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('DockerContainers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
