<?php

namespace NextDeveloper\IAAS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class ComputeComputeMemberEventsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ComputeMemberEvents $event) {
        $this->queue = 'iaas-health-check';
    }

    public function handle(): void
    {
        $event = json_decode($this->event->event, true);

        $results = [];;

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

        //  We dont need to update ISO SR events
        if($event['class'] == 'sr' && $event['operation'] == 'mod') {
            if($event['snapshot']['type'] == 'iso') {
                $this->event->forceDelete();
                return;
            }
        }

        //  We dont need to compute DEL => Delete events
        if($event['operation'] == 'del') {
            $this->event->forceDelete();
            return;
        }

        //  We dont need SR.scan events, therefore we can delete
        if(array_key_exists('name_label', $event['snapshot'])) {
            if($event['snapshot']['name_label'] == 'SR.scan') {
                $this->event->forceDelete();
                return;
            }
        }

        if(!array_key_exists('name', $event['snapshot'])) {
            $results = array_merge($results, ['error' => 'Event snapshot does not contain a name']);
        } else {
            switch ($event['snapshot']['name']) {
                case 'VM_SHUTDOWN':
                case 'VM_STARTED':
                    $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                        ->where('hypervisor_uuid', $event['snapshot']['obj_uuid'])
                        ->withTrashed()
                        ->first();
                    $healthCheck = new HealthCheck($vm);
                    dispatch($healthCheck);
                    $results = array_merge($results, ['executed'  =>  'Initiated health check for VM with hypervisor_uuid: ' . $event['snapshot']['obj_uuid']]);
                    break;
                default:
                    $results = array_merge($results, ['skipped'  =>  'Event type not handled: ' . $event['snapshot']['name']]);
                    Log::info(__METHOD__ . ': Skipped event type ' . $event['snapshot']['name'] . ' for event ID ' . $this->event->id);
                    break;
            }
        }

        $this->event->results = $results;
        $this->event->is_executed = true;
        $this->event->saveQuietly();
    }
}
