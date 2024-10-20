<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Actions\VirtualNetworkCards\Attach;
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
        $vm = null;

        if(!array_key_exists('iaas_virtual_machine_id', $data)) {
            Log::error(__METHOD__ . ' | I have a network card creation request without a VM id. ' .
                'I cannot allow that. Sorry.');

            return null;
        }

        if(array_key_exists('iaas_virtual_machine_id', $data)) {
            if(Str::isUuid($data['iaas_virtual_machine_id']))
                $vm = VirtualMachines::where('uuid', $data['iaas_virtual_machine_id'])->first();
            else
                $vm = VirtualMachines::where('id', $data['iaas_virtual_machine_id'])->first();

            if(!$vm) {
                //  If we still cannot find the virtual machine, this means that either this machine is deleted in the database
                //  or the virtual machine is not owned by the executer
                if(Str::isUuid($data['iaas_virtual_machine_id'])) {
                    $vm = VirtualMachines::withoutGlobalScopes()
                        ->where('uuid', $data['iaas_virtual_machine_id'])
                        ->first();
                }
                else {
                    $vm = VirtualMachines::withoutGlobalScopes()
                        ->where('id', $data['iaas_virtual_machine_id'])
                        ->first();
                }
            }

            if(!$vm) {
                Log::error(__METHOD__ . ' | So I have a data to create virtual network card with ' .
                    'data below. But I cannot find the VM. This is kind of weird, ' .
                    'that is why I am putting the data here too;' . print_r($data, true));

                Log::error(__METHOD__ . ' | Highly likely the VM is in the database but it is set as ' .
                    'deleted. We may need to revive the VM.');

                return null;
            }

            $vifs = VirtualNetworkCards::where('iaas_virtual_machine_id', $vm->id)->get();

            //  We need to create a unique device number for the new VIF
            $data['device_number']  =  count($vifs);
        }

        $vif = parent::create($data);

        dispatch(new Attach($vif));

        return $vif;
    }
}
