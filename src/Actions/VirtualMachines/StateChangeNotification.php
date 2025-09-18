<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachines;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\Communication\Helpers\Communicate;
use App\Envelopes\IAAS\SendStateChangeNotification;

class StateChangeNotification extends AbstractAction
{
    /**
     * Fully-qualified event name used to select the notification message.
     */
    protected ?string $eventName;

    /**
     * Map of VM lifecycle event identifiers to email body templates.
     * The token `:vm_name` will be replaced with the display name of the VM.
     */
    private const EVENT_MESSAGES = [
        'paused:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has been completed successfully. Your virtual machine is now paused. \n\n Virtual machine name: :vm_name",
        'pause-failed:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has failed. Your virtual machine could not be paused. \n\n Virtual machine name: :vm_name",
        'restarted:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has been completed successfully. Your virtual machine has been restarted. \n\n Virtual machine name: :vm_name",
        'restart-failed:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has failed. Your virtual machine could not be restarted. \n\n Virtual machine name: :vm_name",
        'started:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has been completed successfully. Your virtual machine has been started. \n\n Virtual machine name: :vm_name",
        'start-failed:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has failed. Your virtual machine could not be started. \n\n Virtual machine name: :vm_name",
        'backed-up:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has been completed successfully. Your virtual machine has been backed up. \n\n Virtual machine name: :vm_name",
        'backup-failed:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has failed. Your virtual machine could not be backed up. \n\n Virtual machine name: :vm_name",
        'committed:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has been completed successfully. Your virtual machine has been committed. \n\n Virtual machine name: :vm_name",
        'commit-failed:NextDeveloper\IAAS\VirtualMachines' => "The action you requested has failed. Your virtual machine could not be committed. \n\n Virtual machine name: :vm_name",
    ];

    /**
     * @throws NotAllowedException
     */
    public function __construct($model, array $params = [], $previous = null)
    {
        $this->model = $model;
        $this->eventName = $params['event'] ?? null;
        parent::__construct($previous);
    }

    public function handle(): void
    {
        $this->setProgress(0, 'Preparing VM status change notification.');

        // Get user
        $this->setProgress(10, 'Fetching target user for notification.');
        if (!isset($this->model->iam_user_id)) {
            $this->setFinishedWithError('Cannot determine the user to send notification to.');
            return;
        }

        $user = Users::withoutGlobalScope(AuthorizationScope::class)
            ->find($this->model->iam_user_id);

        if (!$user) {
            $this->setFinishedWithError('Cannot find the user to send notification to.');
            return;
        }

        $this->setProgress(40, 'User found. Building notification envelope.');

        try {
            $bodyString = str_replace(':vm_name', $this->getVmDisplayName(), $this->resolveEventBody());


            $this->setProgress(70, 'Dispatching notification email.');
            (new Communicate($user))
                ->sendEnvelopeNow(new SendStateChangeNotification($this->model, $user, $bodyString));

            $this->setProgress(100, 'Notification email sent successfully.');
            $this->setFinished('Virtual machine status change notification sent.');
        } catch (\Throwable $e) {
            $this->setFinishedWithError('Failed to send status change notification: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the email body string for the given event.
     * Falls back to a generic message if the event is not listed.
     */
    private function resolveEventBody(): string
    {
        return self::EVENT_MESSAGES[$this->eventName] ?? "The status of your virtual machine has changed. \n\n Virtual machine name: :vm_name";
    }


    /**
     * Get a friendly VM display name
     */
    private function getVmDisplayName(): string
    {
        return (string) ($this->model->name ?? 'your virtual machine');
    }
}
