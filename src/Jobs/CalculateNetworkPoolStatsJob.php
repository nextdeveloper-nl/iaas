<?php

namespace NextDeveloper\IAAS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CalculateNetworkPoolStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Retrieve all network pools
        $networkPools = NetworkPools::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each network pool
        foreach ($networkPools as $networkPool)
        {
            // Calculate the count of networks with VLANs in this pool
            $vlanCount = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_network_pool_id', $networkPool->id)
                ->where('vlan', '<=', 0)
                ->count();

            // Calculate the count of networks with VXLANs in this pool
            $vxlanCount = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_network_pool_id', $networkPool->id)
                ->where('vxlan', '>', "0")
                ->count();

            // Insert the calculated statistics into the database
            // Using a single insert operation for better performance
            DB::table('iaas_network_pool_stats')->insert([
                'iaas_network_pool_id'  => $networkPool->id,
                'used_vlan'             => $vlanCount,
                'used_vxlan'            => $vxlanCount,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }
}
