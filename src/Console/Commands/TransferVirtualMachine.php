<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class TransferVirtualMachine extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:transfer-vm {--vm-id=} {--account-id=}';

    /**
     * @var string
     */
    protected $description = 'Binds the events to the listeners for IAAS module only.';

    //  php artisan leo:bind-events

    /**
     * @return void
     */
    public function handle() {
        $this->line('Migrating virtual machine');
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $this->option('vm-id'))
            ->first();

        $account = Accounts::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $this->option('account-id'))
            ->first();

        if(!$vm)
            $this->error('Cannot find the VM with that ID. Are you sure that you are providing UUID?');

        if(!$account)
            $this->error('Cannot find the account with that ID. Are you sure that you are providing UUID?');

        $vm->updateQuietly([
            'iam_account_id' => $account->id
        ]);

        Log::info('Virtual machine ' . $vm->uuid . ' has been transferred to ' . $account->uuid);

        //  Now we will migrate disks

        $vdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();

        foreach ($vdi as $disk) {
            $disk->updateQuietly([
                'iam_account_id' => $account->id
            ]);

            Log::info('Disk ' . $disk->uuid . ' has been transferred to ' . $account->uuid);
            $this->line('Disk ' . $disk->uuid . ' has been transferred to ' . $account->uuid);
        }

        //  Now we will migrate backups

        $backups = VirtualMachineBackups::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();

        foreach ($backups as $backup) {
            $backup->updateQuietly([
                'iam_account_id' => $account->id
            ]);

            Log::info('Backup ' . $backup->uuid . ' has been transferred to ' . $account->uuid);
            $this->line('Backup ' . $backup->uuid . ' has been transferred to ' . $account->uuid);
        }

        // Now we will migrate network cards

        $networkCards = VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();

        foreach ($networkCards as $networkCard) {
            $networkCard->updateQuietly([
                'iam_account_id' => $account->id
            ]);

            Log::info('Network card ' . $networkCard->uuid . ' has been transferred to ' . $account->uuid);
            $this->line('Network card ' . $networkCard->uuid . ' has been transferred to ' . $account->uuid);
        }
    }
}
