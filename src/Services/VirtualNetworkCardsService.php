<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualNetworkCardsService;

/**
 * This class is responsible from managing the data for VirtualNetworkCards
 *
 * Class VirtualNetworkCardsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualNetworkCardsService extends AbstractVirtualNetworkCardsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function create($data)
    {
        if(array_key_exists('iaas_virtual_machine_id', $data)) {
            $vm = null;

            if(Str::isUuid($data['iaas_virtual_machine_id']))
                $vm = VirtualMachines::where('uuid', $data['iaas_virtual_machine_id'])->first();
            else
                $vm = VirtualMachines::where('id', $data['iaas_virtual_machine_id'])->first();

            $vm->update([
                'status'    =>  'pending-update'
            ]);

            $vifs = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

            //  We need to create a unique device number for the new VIF
            $data['device_number']  =  count($vifs);
        }

        return parent::create($data);
    }
}
