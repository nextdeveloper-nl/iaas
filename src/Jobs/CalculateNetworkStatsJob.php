<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CalculateNetworkStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Retrieve all networks
        $networks = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each network
        foreach ($networks as $network) {
            // Count the number of IP addresses associated with the network
            $ipAddressCount = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_network_id', $network->id)
                ->count();

            // Insert network statistics into the database
            DB::table('iaas_network_stats')->insert([
                'iaas_network_id'   => $network->id,
                'total_tx'          => 0,
                'total_rx'          => 0,
                'total_ip_address'  => $ipAddressCount,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}
