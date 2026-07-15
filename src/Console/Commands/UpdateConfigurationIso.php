<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Contracts\ConfigurationIsoCapableInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAAS\Services\HypervisorsV2\VirtualMachineManager;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class UpdateConfigurationIso extends Command
{
    /**
     * @var string
     */
    protected $signature = 'leo:update-configuration-iso {uuid : The UUID of the virtual machine}';

    /**
     * @var string
     */
    protected $description = 'Regenerates and uploads the configuration ISO for the virtual machine with the given UUID.';

    /**
     * @return void
     */
    public function handle()
    {
        $uuid = $this->argument('uuid');

        $this->line('Looking up virtual machine: ' . $uuid);

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('uuid', $uuid)
            ->first();

        if (!$vm) {
            $this->error('Virtual machine not found with UUID: ' . $uuid);
            Log::error(__METHOD__ . ' | Cannot find VM with uuid: ' . $uuid);
            return;
        }

        $this->line('Found VM: ' . $vm->name . ' (' . $vm->uuid . ')');

        UserHelper::setUserById($vm->iam_user_id);
        UserHelper::setCurrentAccountById($vm->iam_account_id);

        $this->line('Updating configuration ISO...');
        Log::info(__METHOD__ . ' | Updating configuration ISO for VM: ' . $vm->uuid);

        try {
            $driver = app(VirtualMachineManager::class)->getAdapter($vm);

            $result = $driver instanceof ConfigurationIsoCapableInterface
                ? $driver->regenerateConfigurationIso($vm)
                : VirtualMachinesXenService::updateConfigurationIso($vm);

            if ($result) {
                $this->info('Configuration ISO updated successfully for VM: ' . $vm->name);
                Log::info(__METHOD__ . ' | Configuration ISO updated successfully for VM: ' . $vm->uuid);
            } else {
                $this->warn('Configuration ISO update returned false — no ISO repository found for VM: ' . $vm->name);
                Log::warning(__METHOD__ . ' | No ISO repository found for VM: ' . $vm->uuid);
            }
        } catch (\Exception $e) {
            $this->error('Failed to update configuration ISO: ' . $e->getMessage());
            Log::error(__METHOD__ . ' | Failed to update configuration ISO for VM: ' . $vm->uuid . '. Error: ' . $e->getMessage());
        }
    }
}
