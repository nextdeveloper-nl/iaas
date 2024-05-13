<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class CalculateVirtualNetworkCardStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Retrieve all virtual network cards
        $virtualNetworkCards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        // Loop through each virtual network card
        foreach ($virtualNetworkCards as $virtualNetworkCard)
        {
            // Insert virtual network card statistics into the database
            DB::table('iaas_virtual_network_card_stats')->insert([
                'iaas_virtual_network_card_id'  => $virtualNetworkCard->id,
                'used_tx'                       => 0,
                'used_rx'                       => 0,
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ]);
        }
    }
}
