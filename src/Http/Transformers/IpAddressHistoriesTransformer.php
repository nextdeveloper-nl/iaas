<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IpAddressHistories;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIpAddressHistoriesTransformer;

/**
 * Class IpAddressHistoriesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IpAddressHistoriesTransformer extends AbstractIpAddressHistoriesTransformer
{

    /**
     * @param IpAddressHistories $model
     *
     * @return array
     */
    public function transform(IpAddressHistories $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('IpAddressHistories', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IpAddressHistories', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
