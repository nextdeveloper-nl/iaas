<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\ComputeMembers\CheckServices;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class ComputeMemberServiceCheck extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:cm-service-check';

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

        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->get();

        foreach ($computeMembers as $computeMember) {
            Log::info(__METHOD__ . ' | Started service check for compute member: ' . $computeMember->uuid);

            try {
                $job = new CheckServices($computeMember);
                $id = $job->getActionId();

                dispatch($job)->onQueue('iaas-health-check');
            } catch (\Exception $e) {
                Log::error(__METHOD__ . " | Having a problem with service check of compute member: " . $computeMember->uuid);
            }
        }
    }
}
