<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMigrations;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachineMigrationsTransformer;

/**
 * Class VirtualMachineMigrationsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachineMigrationsTransformer extends AbstractVirtualMachineMigrationsTransformer
{

    /**
     * @param VirtualMachineMigrations $model
     *
     * @return array
     */
    public function transform(VirtualMachineMigrations $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachineMigrations', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachineMigrations', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }
}
