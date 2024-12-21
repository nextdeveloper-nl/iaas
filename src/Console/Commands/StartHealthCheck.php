<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
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

        $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('is_draft', false)
            ->get();

        foreach ($vms as $vm) {
            Log::info(__METHOD__ . ' | Started health check for VM: ' . $vm->uuid);

            //  We are setting this because we want this action to be able run all the times!!!!
            UserHelper::setUserById($vm->iam_user_id);
            UserHelper::setCurrentAccountById($vm->iam_account_id);

            try {
                $job = new HealthCheck($vm);
                $id = $job->getActionId();

                dispatch($job)->onQueue('iaas-health-check');
            } catch (\Exception $e) {
                Log::error(__METHOD__ . " | Having a problem with health check of vm: " . $vm->uuid);
            }
        }
    }
}
