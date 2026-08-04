<?php

namespace NextDeveloper\IAAS\Jobs\GarbageCollectors;

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
        //  This should not work
        trigger_deprecation('nextdeveloper/iaas', '1.0', 'The %s class is deprecated and will be removed in the next major version.', __CLASS__);

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

                //  Dispatch on ComputePools.virtualization, not the per-host free-text
                //  hypervisor_model (which previously had to enumerate every known XenServer
                //  minor version string) - see docs/hypervisor-driver-architecture.md.
                switch ($computeMember->computePools?->virtualization) {
                    case 'xenserver-8.2':
                    case 'xenserver-8.2-ssh':
                    case 'xcp-ng-8.2':
                    case 'xcp-ng-8.2-ssh':
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
