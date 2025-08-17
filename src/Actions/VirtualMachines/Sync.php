<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Jobs\VirtualMachines\Fix;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualNetworkCardsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action converts the virtual machine into a template
 */
class Sync extends AbstractAction
{
    public const EVENTS = [
        'syncing:NextDeveloper\IAAS\VirtualMachines',
        'synced:NextDeveloper\IAAS\VirtualMachines',
        'sync-failed:NextDeveloper\IAAS\VirtualMachines'
    ];

    public function __construct(VirtualMachines $vm)
    {
        $this->model = $vm;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate virtual machine sync');

        if($this->model->is_lost) {
            $this->setFinished('Unfortunately this vm is lost, that is why we cannot continue.');
            return;
        }

        if($this->model->deleted_at != null) {
            $this->setFinished('I cannot complete this process because the VM is already deleted');
            return;
        }

        (new Fix($this->model))->handle();

        Events::fire('syncing:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $params = VirtualMachinesXenService::getVmParameters($this->model);

        if(!array_key_exists('power-state', $params)) {
            //  The VM must not be available to be honest. So we should make a health check here.
            $this->model->update([
                'status'    =>  'checking-health'
            ]);

            $job = new HealthCheck($this->model, null, $this);
            $id = $job->getActionId();

            dispatch($job)->onQueue('iaas');

            $this->setProgress(100, 'Checking the health of the VM. ' .
                'We suspect something is happening to it.');

            return $id;
        }

        if(!$params) {
            $this->setProgress(100, 'Virtual machine failed to sync');
            Events::fire('sync-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
            return;
        }

        $this->model->update([
            'status'    =>  $params['power-state'],
            'cpu'       =>  $params['VCPUs-max'],
            'ram'       =>  $params['memory-static-max'] / 1024 / 1024 / 1024,
            'is_snapshot'   =>  $params['is-a-snapshot'] === 'true',
            'domain_type'   =>  $params['hvm'] === 'true' ? 'HVM' : 'PV',
            'hypervisor_data'   =>  $params
        ]);

        $this->syncXenVifs();

        Events::fire('synced:NextDeveloper\IAAS\VirtualMachines', $this->model);

        $this->setProgress(100, 'Virtual machine synced');
    }

    public function syncXenVifs()
    {
        $vifs = VirtualMachinesXenService::getVifs($this->model);

        $computeMember = VirtualMachinesService::getComputeMember($this->model);

        foreach ($vifs as $vif) {
            if($vif == [])
                continue;

            $vifParams = VirtualMachinesXenService::getVifParams($this->model, $vif['uuid']);

            if(array_key_exists(0, $vifParams))
                $vifParams = $vifParams[0];

            $dbVif = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $vif['uuid'])
                ->trashed()
                ->first();

            if($dbVif->isTrashed()) {
                //  If the VIF is trashed, we should restore it
                $dbVif->restore();
            }

            $connectedInterface = ComputeMemberNetworkInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('network_uuid', $vifParams['network-uuid'])
                ->first();

            if(!$connectedInterface) {
                //  Here we will add another trigger to scan all compute member network interfaces
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[ScanVirtualMachines] Cannot find the connected ' .
                    'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                    'should be scanned and synced immediately.');

                continue;
            }

            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('vlan', $connectedInterface->vlan)
                ->where('iaas_cloud_node_id', (VirtualMachinesService::getCloudPool($this->model))->iaas_cloud_node_id)
                ->first();

            if(!$network) {
                //  Here we need to create another scan and create the related network
                StateHelper::setState($computeMember, 'needs_scan', true);

                Log::error('[ScanVirtualMachines] Cannot find the connected ' .
                    'interface for the VIF: ' . $vif['uuid'] . '. This compute member ' .
                    'should be scanned and synced immediately.');

                continue;
            }

            $data = [
                'name'          =>  'eth' . $vifParams['device'],
                'device_number' => $vifParams['device'],
                'mac_addr'      => $vifParams['MAC'],
                'bandwidth_limit'   => '-1', //$vifParams['qos_algorithm_params']['kbps'],
                'iaas_network_id'       => $network->id,
                'hypervisor_uuid'   => $vif['uuid'],
                'hypervisor_data'   => $vifParams,
                'iam_account_id'    => $this->model->iam_account_id,
                'iam_user_id'       => $this->model->iam_user_id,
                'is_draft'          => false,
                'status'            => 'attached:true',
                'iaas_virtual_machine_id'   =>  $this->model->id
            ];

            if($dbVif)
                $dbVif->updateQuietly($data);
            else
                VirtualNetworkCardsService::create($data);
        }
    }
}
