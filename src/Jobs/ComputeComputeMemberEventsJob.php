<?php

namespace NextDeveloper\IAAS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class ComputeComputeMemberEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $vm;

    public function __construct(public ComputeMemberEvents $event) {
        $this->queue = 'iaas-health-check';
    }

    public function handle(): void
    {
        $event = json_decode($this->event->event, true);

        $results = [];

        if(!$event) {
            Log::error( __METHOD__ . ': Invalid event data for event ID ' . $this->event->id);
            $this->event->is_executed = true;
            $this->event->results = [
                'error' =>  'Invalid event data format',
            ];
            $this->event->saveQuietly();
            return;
        }

        UserHelper::setAdminAsCurrentUser();

        if($event['class'] == 'message') {
            switch ($event['operation']) {
                case 'add':
                    //  We will handle message add events here
                    $results = array_merge($results, $this->messageAddOperation());
                    break;
            }
        }

        if($event['class'] == 'vm') {
            $this->vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $event['snapshot']['uuid'])
                ->withTrashed()
                ->first();

            //  Because we need the users privileges to update the VM
            UserHelper::setUserById($this->vm->user_id);
            UserHelper::setCurrentAccountById($this->vm->account_id);

            //  We will handle VM events here
            switch ($event['operation']) {
                case 'add':
                    $results = array_merge($results, $this->vmAddOperation());
                    break;
                case 'mod':
                    //  We will handle VM modification events here
                    $results = array_merge($results, $this->vmModOperation());
                    break;
                case 'del':
                    //  We will handle VM deletion events here
                    $results = array_merge($results, $this->vmDelOperation());
                    break;
                default:
                    Log::info(__METHOD__ . ': Skipped event type ' . $event['operation'] . ' for event ID ' . $this->event->id);
                    $this->event->forceDelete();
                    return;
            }
        }

        if($event['class'] == 'sr') {
            switch ($event['operation']) {
                case 'add':
                    //  We will handle SR add events here
                    $results = array_merge($results, ['executed' => 'SR add event handled']);
                    break;
                case 'mod':
                    //  We will handle SR modification events here
                    $results = array_merge($results, ['executed' => 'SR modification event handled']);
                    break;
                case 'del':
                    //  We will handle SR deletion events here
                    $results = array_merge($results, ['executed' => 'SR deletion event handled']);
                    break;
                default:
                    Log::info(__METHOD__ . ': Skipped event type ' . $event['operation'] . ' for event ID ' . $this->event->id);
                    $this->event->forceDelete();
                    return;
            }
        }

        $this->event->results = $results;
        $this->event->is_executed = true;
        $this->event->saveQuietly();

        //  Now we will remove events that are older than 24 hours and is_executed is false
        ComputeMemberEvents::withoutGlobalScope(AuthorizationScope::class)
            ->where('is_flagged', false)
            ->where('created_at', '<', now()->subDay())
            ->forceDelete();

        //  Removing all events after 30 days
        ComputeMemberEvents::withoutGlobalScope(AuthorizationScope::class)
            ->where('created_at', '<', now()->subDays(30))
            ->forceDelete();
    }

    private function messageAddOperation($results = [])
    {
        $event = json_decode($this->event->event, true);

        switch ($event['snapshot']['name']) {
            case 'VM_SHUTDOWN':
                $results = array_merge($results, ['executed'  =>  'VM is halted with hypervisor_uuid: ' . $event['snapshot']['obj_uuid']]);
                \Log::info(__METHOD__ . ': VM (' . $event['snapshot']['obj_uuid'] . ') shutdown event handled for event ID ' . $this->event->id);
                break;
            case 'VM_STARTED':
                \Log::info(__METHOD__ . ': VM (' . $event['snapshot']['obj_uuid'] . ') started for event ID ' . $this->event->id);
                break;
            case 'VM_REBOOTED':
                \Log::info(__METHOD__ . ': VM (' . $event['snapshot']['obj_uuid'] . ') rebooted for event ID ' . $this->event->id);
                $this->event->is_flagged = true;
                break;
            default:
                $results = array_merge($results, ['skipped'  =>  'Event type not handled: ' . $event['snapshot']['name']]);
                Log::info(__METHOD__ . ': Skipped event type ' . $event['snapshot']['name'] . ' for event ID ' . $this->event->id);
                break;
        }

        return $results;
    }

    private function vmAddOperation($results = [])
    {
        return [];
    }

    private function vmModOperation()
    {
        $event = json_decode($this->event->event, true);

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $event['snapshot']['uuid'])
            ->withTrashed()
            ->first();

        $vm->update([
            'status'    =>  strtolower($event['snapshot']['power_state']),
            'ram'       =>  intval($event['snapshot']['memory_dynamic_min']) / 1024 / 1024, // Convert from bytes to MB
            'cpu'       =>  $event['snapshot']['VCPUs_max'],
            'domain_type'   =>  $event['snapshot']['domain_type'],
        ]);

        $event->is_flagged = true;

        \Log::info(__METHOD__ . ': VM modified with hypervisor_uuid: ' . $event['snapshot']['uuid'] . ' for event ID ' . $this->event->id);

        return [];
    }

    private function vmDelOperation() {
        $this->event->forceDelete();

        return [];
    }

    private function srAddOperation()
    {
        //  We will handle SR add events here
        return [];
    }

    private function srModOperation()
    {
        //  We will handle SR modification events here
        $this->event->forceDelete();

        return [];
    }

    private function srDelOperation()
    {
        //  We will handle SR deletion events here
        $this->event->forceDelete();

        return [];
    }
}
