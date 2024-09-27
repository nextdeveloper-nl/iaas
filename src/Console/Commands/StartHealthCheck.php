<?php
namespace NextDeveloper\IAAS\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class StartHealthCheck extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:vm-health-check';

    /**
     * @var string
     */
    protected $description = 'Binds the events to the listeners for IAAS module only.';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        $this->line('Starting for routine health check.');
        Log::info(__METHOD__ . 'Starting for routine health check.');

        $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)->get();

        foreach ($vms as $vm) {
            Log::info(__METHOD__ . ' | Started health check for VM: ' . $vm->uuid);
            UserHelper::setUserById(config('leo.current_user_id'));
            UserHelper::setCurrentAccountById(config('leo.current_account_id'));

            $job = new HealthCheck($vm);
            $id = $job->getActionId();

            dispatch($job)->onQueue('iaas-health-check');
        }
    }
}
