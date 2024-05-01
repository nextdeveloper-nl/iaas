<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractIpAddressesTransformer;

/**
 * Class IpAddressesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class IpAddressesTransformer extends AbstractIpAddressesTransformer
{

    /**
     * @param IpAddresses $model
     *
     * @return array
     */
    public function transform(IpAddresses $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('IpAddresses', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('IpAddresses', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
