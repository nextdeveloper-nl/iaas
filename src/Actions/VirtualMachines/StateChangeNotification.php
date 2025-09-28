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
        'paused:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine is now <b>PAUSED</b>"
        ],
        'pause-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>PAUSED</b>"
        ],
        'restarted:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine has been <b>RESTARTED</b>"
        ],
        'restart-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>RESTARTED</b>"
        ],
        'started:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine has been <b>STARTED</b>"
        ],
        'start-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>STARTED</b>."
        ],
        'backed-up:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine has been <b>BACKED UP</b>."
        ],
        'backup-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>BACKED UP</b>."
        ],
        'committed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine has been <b>COMMITTED</b>."
        ],
        'commit-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>COMMITTED</b>."
        ],
        'health-check-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine has failed the health check."
        ],
        'cleaned-up:NextDeveloper\IAAS\VirtualMachines' => [
            'success'   =>  false,
            'message'   =>  'Your virtual machine has been cleaned up due to falty state.'
        ],
        'halted:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine is now <b>HALTED</b>."
        ],
        'shutdown-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>HALTED</b>."
        ],
        'unpaused:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine is now <b>RUNNING</b> after being unpaused."
        ],
        'unpause-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>UNPAUSED</b>."
        ],
        'deleted:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  true,
            'message'   =>  "Your virtual machine has been <b>DELETED</b>."
        ],
        'delete-failed:NextDeveloper\IAAS\VirtualMachines' => [
            'success'    =>  false,
            'message'   =>  "Your virtual machine could not be <b>DELETED</b>."
        ]
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
        $event =  self::EVENT_MESSAGES[$this->eventName];

        return $event['success']
           ?  "The action you requested has been completed successfully. {$event['message']}. \n\n The affected Virtual machine is: <b>:vm_name</b>"
           : "The action you requested has failed. {$event['message']}. \n\n Please try again later. if the problem persists, contact support. \n\n The affected Virtual machine is: <b>:vm_name</b>";
    }

    /**
     * Get a friendly VM display name
     */
    private function getVmDisplayName(): string
    {
        return (string) ($this->model->name ?? 'your virtual machine');
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
