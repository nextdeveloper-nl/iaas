<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

class CommentVMActionEvent extends AbstractAction
{
    /**
     * Map of VM lifecycle event identifiers to comment templates.
     * The token `:vm_name` will be replaced with the display name of the VM.
     */
    private const EVENT_MESSAGES = [
        // Backup lifecycle
        'backing-up:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being backed up.',
        'backed-up:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been backed up.',
        'backup-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name backup has failed.',

        // Template conversion
        'converting-to-template:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being converted to a template.',
        'converted-to-template:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been converted to a template.',
        'conversion-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name conversion has failed.',

        // Commit
        'commiting:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being committed.',
        'committed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been committed.',
        'commit-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name commit has failed.',

        // Delete
        'deleting:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being deleted.',
        'deleted:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been deleted.',
        'delete-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name deletion has failed.',

        // CD operations
        'ejecting-cd:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is ejecting its CD.',
        'cd-ejected:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name CD has been ejected.',
        'mounting-cd:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is mounting a CD.',
        'cd-mounted:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name CD has been mounted.',
        'mounting-cd-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name CD mounting has failed.',

        // Export
        'exporting:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being exported.',
        'exported:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been exported.',
        'export-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name export has failed.',

        // Plug/Unplug
        'plugging:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being plugged.',
        'plugged:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been plugged.',
        'unplugging:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being unplugged.',
        'unplugged:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been unplugged.',
        'unplug-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name unplug operation has failed.',

        // Health & check
        'checking:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being checked.',
        'checked:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been checked.',
        'healthy:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is healthy.',
        'health-check-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name health check has failed.',
        'vm-is-lost:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is lost.',

        // Locking
        'locking:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being locked.',
        'locked:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been locked.',

        // Pause
        'pausing:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being paused.',
        'paused:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been paused.',
        'pause-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name pausing has failed.',
        'unpausing:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being unpaused.',
        'unpaused:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been unpaused.',
        'unpause-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name unpausing has failed.',

        // Restart
        'restarting:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being restarted.',
        'restarted:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been restarted.',
        'restart-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name restart has failed.',

        // Halt & Stop & Run
        'halting:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is being halted.',
        'halted:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been halted.',
        'stopped:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been stopped.',
        'running:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is running.',
        'shutdown-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name shutdown has failed.',

        // Snapshot
        'taking-snapshot:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is taking a snapshot.',
        'snapshot-taken:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name snapshot has been taken.',
        'snapshot-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name snapshot has failed.',

        // Start
        'starting:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is starting.',
        'started:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been started.',
        'start-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name start has failed.',

        // Sync
        'syncing:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name is syncing.',
        'synced:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name has been synced.',
        'sync-failed:NextDeveloper\IAAS\VirtualMachines' => 'Virtual machine :vm_name syncing has failed.',
    ];
    /**
     * Fully-qualified event name used to select the notification message.
     */
    protected ?string $eventName;

    public function __construct(VirtualMachines $vm, $params = [], $previous = null)
    {
        $this->model = $vm;
        $this->eventName = $params['event'] ?? null;

        $this->queue = 'iaas';

        parent::__construct($previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'State change notification job started.');

        if ($this->eventName === null || !array_key_exists($this->eventName, self::EVENT_MESSAGES)) {
            $this->setFinished(__METHOD__ . ': Event name is either not provided or not supported.');
            return;
        }

        $vmName = $this->resolveVmName();
        $message = str_replace(':vm_name', $vmName, self::EVENT_MESSAGES[$this->eventName]);

        try {
            CommentsService::createSystemComment(
                $message,
                $this->model,
            );

            $this->setFinished('State change notification process completed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create state change comment: ' . $e->getMessage());
            $this->setFinished(__METHOD__ . ': Failed to create state change comment: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the best human-readable VM identifier.
     */
    private function resolveVmName(): string
    {
        $candidates = [
            'display_name',
            'name',
            'hostname',
            'uuid'
        ];

        foreach ($candidates as $field) {
            if (isset($this->model->$field) && !empty($this->model->$field)) {
                return (string) $this->model->$field;
            }
        }

        return 'N/A';
    }

    /**
     * Returns the list of fully-qualified event keys this action can handle.
     * Keeping this logic here prevents duplication elsewhere when binding events.
     */
    public static function getSupportedEvents(): array
    {
        return array_keys(self::EVENT_MESSAGES);
    }
}



