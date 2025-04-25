<?php

namespace NextDeveloper\IAAS\Jobs\GarbageCollectors;

use GPBMetadata\Google\Api\Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\ComputeMemberNetworkInterfaces;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CollectGarbageNetworks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        //  Checking this because we dont want to delete networks which have network cards in it.
        $virtualNetworkCards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->pluck('iaas_network_id');

        // Retrieve all networks
        $networks = Networks::withoutGlobalScopes()
            ->where('vlan', '!=', '-1')
            ->whereNotIn('id', $virtualNetworkCards)
            ->where('name', 'not like', 'Pool-wide%')
            ->whereNotNull('deleted_at')
            ->get();

        foreach ($networks as $network) {
            $computeMemberNetworks = ComputeMemberNetworkInterfaces::withoutGlobalScopes(AuthorizationScope::class)
                ->withoutGlobalScope(LimitScope::class)
                ->where('vlan', $network->vlan)
                ->get();

            foreach ($computeMemberNetworks as $computeMemberNetwork) {
                $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
                    ->withoutGlobalScope(LimitScope::class)
                    ->where('id', $computeMemberNetwork->iaas_compute_member_id)
                    ->first();

                switch ($computeMember->hypervisor_model) {
                    case 'XenServer 8.2':
                    case 'XenServer 8.1':
                    case 'XenServer 8.0':
                    case 'XenServer 7.2':
                    case 'XenServer 7.1':
                    case 'XenServer 7.0':
                    case 'XenServer 6.5':
                    case 'XenServer 6.2':
                        ComputeMemberXenService::deleteNetwork($computeMember, $computeMemberNetwork);
                        break;
                }
            }

            DB::update(
                'UPDATE iaas_networks SET deleted_at = ? WHERE id = ?',
                [now(), $network->id]
            );

            DB::update(
                'UPDATE iaas_compute_member_network_interfaces SET deleted_at = ? WHERE id = ?',
                [now(), $computeMemberNetwork->id]
            );
        }
    }
}
