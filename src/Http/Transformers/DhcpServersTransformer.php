<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractDhcpServersTransformer;

/**
 * Class DhcpServersTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class DhcpServersTransformer extends AbstractDhcpServersTransformer
{

    /**
     * @param DhcpServers $model
     *
     * @return array
     */
    public function transform(DhcpServers $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('DhcpServers', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('DhcpServers', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
