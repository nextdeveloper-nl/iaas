<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Actions\VirtualMachines\Sync;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncVirtualMachine extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:sync-virtual-machine {--uuid=} {--fg=}';

    /**
     * @var string
     */
    protected $description = 'Sync virtual machines with the hypervisor. This command is used to sync the virtual machines with the hypervisor and to check their health status.';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        $this->line('Starting sync for virtual machines.');
        Log::info(__METHOD__ . 'Starting for routine health check.');

        if($this->option('uuid')) {
            $this->line('Starting sync for virtual machine with uuid: ' . $this->option('uuid'));

            $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                ->withoutGlobalScope(LimitScope::class)
                ->where('uuid', $this->option('uuid'))
                ->get();

            if(!$vms) {
                Log::error(__METHOD__ . ' | Cannot find VM with uuid: ' . $this->option('uuid'));
                return;
            }
        } else {
            $this->line('Starting sync for virtual machines.');

            $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                ->withoutGlobalScope(LimitScope::class)
                ->where('is_draft', false)
                ->get();
        }

        foreach ($vms as $vm) {
            Log::info(__METHOD__ . ' | Started health check for VM: ' . $vm->uuid);

            //  We are setting this because we want this action to be able run all the times!!!!
            UserHelper::setUserById($vm->iam_user_id);
            UserHelper::setCurrentAccountById($vm->iam_account_id);

            try {
                $job = new Sync($vm);
                $id = $job->getActionId();

                if($this->option('fg')) {
                    $job->handle();
                } else {
                    dispatch($job)->onQueue('iaas-misc');
                }
            } catch (\Exception $e) {
                Log::error(__METHOD__ . " | Having a problem with sync of vm: " . $vm->uuid);
            }
        }
    }
}
