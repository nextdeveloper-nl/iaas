<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\SshPublicKeyVirtualMachines;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractSshPublicKeyVirtualMachinesTransformer;

/**
 * Class SshPublicKeyVirtualMachinesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class SshPublicKeyVirtualMachinesTransformer extends AbstractSshPublicKeyVirtualMachinesTransformer
{

    /**
     * @param SshPublicKeyVirtualMachines $model
     *
     * @return array
     */
    public function transform(SshPublicKeyVirtualMachines $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('SshPublicKeyVirtualMachines', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('SshPublicKeyVirtualMachines', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
