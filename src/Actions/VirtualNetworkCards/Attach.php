<?php

namespace NextDeveloper\IAAS\Actions\VirtualNetworkCards;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\NetworkNotInPoolException;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action converts the virtual machine into a template
 */
class Attach extends AbstractAction
{
    public const EVENTS = [
        'attaching:NextDeveloper\IAAS\VirtualNetworkCards',
        'attached:NextDeveloper\IAAS\VirtualNetworkCards',
        'attach-failed:NextDeveloper\IAAS\VirtualNetworkCards'
    ];

    public function __construct(VirtualNetworkCards $vif, $params = null, $previous = null)
    {
        $this->model = $vif;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual network card attach');

        $this->setProgress(5, 'Getting all the information to complete this process');

        $vif = $this->model;

        Events::fire('attaching:NextDeveloper\IAAS\VirtualNetworkCards', $vif);

        if($vif->hypervisor_uuid != null) {
            $this->setFinished('Network card is already attached.');

            $vif->updateQuietly([
                'status'    =>  'attached'
            ]);

            Events::fire('attached:NextDeveloper\IAAS\VirtualNetworkCards', $vif);
            return;
        }

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vif->iaas_virtual_machine_id)
            ->first();

        $computeMember = VirtualMachinesService::getComputeMember($vm);
        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vif->iaas_network_id)
            ->first();

        //  We need to check if the vm exists
        if(!$vm) {
            Log::error('We cannot find the virtual machine for the network card with uuid: ' . $vif->uuid);
            $this->setFinishedWithError('Cannot find the virtual machine of this network card.');
        }

        //  We need to check if the network exists on that cloud pool.
        $this->setProgress(10, 'Validating the networking information. Not the IP settings.');

        $cloudNode = VirtualMachinesService::getCloudPool($vm);

        if(!CloudNodesService::networkExists($cloudNode, $network)) {
            Log::error(__METHOD__ . ' | The network that you are trying to create is not exists on this ' .
                'cloud node, there fore this network pool. Please make sure that you can connect this ' .
                'VM (' . $vm->uuid . ' to this network: ' . $network->name);

            throw new NetworkNotInPoolException('The network that you are trying to create is not exists on this ' .
                'cloud node, there fore this network pool. Please make sure that you can connect this ' .
                'VM (' . $vm->uuid . ' to this network: ' . $network->name);
        }

        //  Checking if the network exists in the hypervisor
        $this->setProgress(15, 'Checking if the network physically exists');

        $interface = ComputeMemberXenService::createNetwork($computeMember, $network);

        $this->setProgress(50, 'Creating the network card with in the related network.');

        $networkCardResult = VirtualMachinesXenService::createVif($vm, $interface->network_uuid, $vif->device_number);

        //  Here we are checking if the result of the network card is UUID
        if(Str::isUuid($networkCardResult)) {
            //  This means that network card is created and attached, we need to sync the network cards
            $vifParams = VirtualMachinesXenService::getVifParams($vm, $networkCardResult);
            $vifParams = $vifParams[0];

            $data = [
                'name'          =>  'eth' . $vifParams['device'],
                'device_number' => $vifParams['device'],
                'mac_addr'      => $vifParams['MAC'],
                'bandwidth_limit'   => '-1', //$vifParams['qos_algorithm_params']['kbps'],
                'iaas_network_id'       => $network->id,
                'hypervisor_uuid'   => $vif['uuid'],
                'hypervisor_data'   => $vifParams,
                'iam_account_id'    => $vm->iam_account_id,
                'iam_user_id'       => $vm->iam_user_id,
                'is_draft'          => false,
                'iaas_virtual_machine_id'   =>  $vm->id,
                'status'    =>  'attached:' . $vifParams['currently-attached']
            ];

            $vif->update($data);

            $this->setProgress(100, 'Network card initiated.');
            return;
        }

        if($networkCardResult[0]['error'] != '') {
            //  We have an error state here, we need to sync the virtual machine
            Events::fire('attach-failed:NextDeveloper\IAAS\VirtualNetworkCards', $vif);
            //  But we need to make VM interface scan.
        }

        $this->setProgress(100, 'Virtual machine initiated');
    }
}
