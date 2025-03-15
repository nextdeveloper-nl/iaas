<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use Illuminate\Support\Facades\Cache;
use League\Fractal\ParamBag;
use NextDeveloper\Commons\Common\Cache\CacheHelper;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Http\Transformers\AbstractTransformers\AbstractVirtualMachinesPerspectiveTransformer;
use NextDeveloper\IAAS\Services\VirtualMachinesService;

/**
 * Class VirtualMachinesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class VirtualMachinesPerspectiveTransformer extends AbstractVirtualMachinesPerspectiveTransformer
{
    public function __construct(ParamBag $paramBag = null)
    {
        $this->addInclude('virtualNetworkCards');
        $this->addInclude('virtualDiskImages');

        return parent::__construct($paramBag);
    }

    /**
     * @param VirtualMachinesPerspective $model
     *
     * @return array
     */
    public function transform(VirtualMachinesPerspective $model)
    {
        $transformed = Cache::get(
            CacheHelper::getKey('VirtualMachinesPerspective', $model->uuid, 'Transformed')
        );

        if($transformed) {
            return $transformed;
        }

        $transformed = parent::transform($model);

        Cache::set(
            CacheHelper::getKey('VirtualMachinesPerspective', $model->uuid, 'Transformed'),
            $transformed
        );

        return $transformed;
    }

    public function includeVirtualNetworkCards(VirtualMachinesPerspective $model)
    {
        $vnc = VirtualNetworkCards::where('iaas_virtual_machine_id', $model->id)
            ->get();

        return $this->collection($vnc, app(VirtualNetworkCardsTransformer::class));
    }

    public function includeVirtualDiskImages(VirtualMachinesPerspective $model)
    {
        $vdi = VirtualDiskImages::where('iaas_virtual_machine_id', $model->id)
            ->get();

        return $this->collection($vdi, app(VirtualDiskImagesTransformer::class));
    }
}
