<?php

namespace NextDeveloper\IAAS\Actions\NetworkMembers;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Services\Switches\DellS6100;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action initates the network member. Make sure that you run this action if you want to change the state of
 * the network member manually.
 */
class Initiate extends AbstractAction
{
    public const EVENTS = [
        'initiated:NextDeveloper\IAAS\NetworkMembers',
        'not-initiated:NextDeveloper\IAAS\NetworkMembers',
    ];

    public function __construct(NetworkMembers $networkMember)
    {
        $this->model = $networkMember;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Network member initiating');

        $this->setProgress(1, 'Updating Interfaces');

        //$this->updateInterfaces(1, 50);
        $this->updateInterfaceConfigurations(50, 99);

        Events::fire('initiated:NextDeveloper\IAAS\NetworkMembers', $this->model);

        $this->setProgress(100, 'Network member initiated');
    }

    private function updateInterfaces($start, $end)
    {
        $switchType = $this->model->switch_type;

        $interfaces = [];

        switch ($switchType) {
            case 'dells6100':
                $interfaces = DellS6100::getInterfaces($this->model);
                break;
        }

        $step = $end - $start;

        for($i = 0; $i < count($interfaces); $i++) {
            $this->setProgress($start + ceil($i * $step / count($interfaces)), 'Updating Interface ' . $interfaces[$i]);

            //  Check if the interface is already in the database
            $interface = NetworkMembersInterfaces::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_network_member_id', $this->model->id)
                ->where('name', $interfaces[$i])
                ->first();

            $network = null;

            if(Str::startsWith($interfaces[$i], 'vlan')) {
                $vlan = $interfaces[$i];
                $vlan = Str::replaceFirst('vlan', '', $vlan);
                $vlan = trim($vlan);

                $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                    ->where('vlan', $vlan)
                    ->where('iaas_network_pool_id', $this->model->iaas_network_pool_id)
                    ->first();
            }

            if($interface) {
                $interface->update([
                    'name'  =>  $interfaces[$i],
                    'iaas_network_id'   =>  $network ? $network->id : null
                ]);
            } else {
                NetworkMembersInterfaces::create([
                    'name'                      =>  $interfaces[$i],
                    'iaas_network_member_id'    =>  $this->model->id,
                    'iaas_network_id'   =>  $network ? $network->id : null
                ]);
            }
        }
    }

    private function updateInterfaceConfigurations($start, $end)
    {
        $interfaces = NetworkMembersInterfaces::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_network_member_id', $this->model->id)
            //->where('name', 'tenGigabitEthernet 1/1/13/2')
            ->get();

        $step = $end - $start;

//        dump(DellS6100::getInterfaceConfiguration($interfaces[0]));
//
//        dd($interfaces);

        for($i = 0; $i < count($interfaces); $i++) {
            $this->setProgress($start + ceil($i * $step / count($interfaces)), 'Updating Interface configuration ' . $interfaces[$i]['name']);

            switch ($this->model->switch_type) {
                case 'dells6100':
                    $configuration = DellS6100::getInterfaceConfiguration($interfaces[$i]);
                    break;
            }

            $interfaces[$i]->update([
                'configuration' =>  $configuration,
                'is_shutdown'   =>  Str::contains($configuration, 'no shutdown') ? false : true
            ]);
        }
    }
}
