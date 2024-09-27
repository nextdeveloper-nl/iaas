<?php
namespace NextDeveloper\IAAS\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class RemoveLostServers extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:vm-remove-lost';

    /**
     * @var string
     */
    protected $description = 'Binds the events to the listeners for IAAS module only.';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        $this->line('Starting for routine garbage collection.');
        Log::info(__METHOD__ . 'Starting for routine garbage collection.');

        $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('is_lost', 'true')
            ->whereNull('deleted_at')
            ->get();

        foreach ($vms as $vm) {
            $isOld = Carbon::now()->subMinutes(15)->greaterThan($vm->created_at);

            if(!$isOld)
                continue;

            Log::info(__METHOD__ . ' Removing the lost VM from the list: ' . $vm->name);

            $vm->deleted_at = now();
            $vm->saveQuietly();
        }
    }
}
