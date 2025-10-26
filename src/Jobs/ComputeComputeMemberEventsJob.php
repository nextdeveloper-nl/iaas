<?php

namespace NextDeveloper\IAAS\Jobs;

use Google\Service\Compute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Commons\Services\CommentsService;
use NextDeveloper\Communication\Helpers\Communicate;
use NextDeveloper\IAAS\Actions\ComputeMembers\ScanVirtualMachines;
use NextDeveloper\IAAS\Actions\ComputeMembers\UpdateResources;
use NextDeveloper\IAAS\Actions\ComputeMembers\UpdateStorageVolumes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputeMemberTasks;
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

        switch ($event['class']) {
            case 'vm':
                $results = array_merge($this->computeVmEvents($event, $results), $results);
                break;
            case 'message':
                $results = array_merge($this->computeMessageEvents($event, $results), $results);
                break;
            case 'sr':
                $results = array_merge($this->computeSrEvents($event, $results), $results);
                break;
            case 'task':
                $results = array_merge($this->computeTaskEvents($event, $results), $results);
                break;
            case 'leo':
                $results = array_merge($this->computeLeoEvents($event, $results), $results);
                break;
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

    private function computeTaskEvents($event, $results = []) : array
    {
        $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('hostname', $event['hostname'])
            ->first();

        if($event['operation'] == 'add') {
            $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
                ->where('hostname', $event['hostname'])
                ->first();

            switch ($event['snapshot']['name_label']) {
                case 'Async.VM.start':
                    dispatch(new UpdateResources($computeMember));
                    break;
                case 'Async.VDI.snapshot':
                case 'VM.snapshot':
                case 'VBD.plug':
                case 'Async.VDI.destroy':
                case 'VM import':
                case 'VDI.create':
                    dispatch(new UpdateStorageVolumes($computeMember));
                    dispatch(new ScanVirtualMachines($computeMember));
                    break;
            }

            if(!$computeMember) {
                Log::error( __METHOD__ . ': Compute member not found for hostname ' . $event['snapshot']['hostname'] . ' for event ID ' . $this->event->id);
                $this->event->forceDelete();
                return $results;
            }

            $snapshot = $event['snapshot'];

            ComputeMemberTasks::create([
                'hypervisor_uuid'   =>  $snapshot['uuid'],
                'status'            =>  $snapshot['status'],
                'name'              =>  $snapshot['name_label'],
                'description'       =>  $snapshot['name_description'],
                'error'             =>  json_encode($snapshot['error_info']),
                'progress'          =>  ceil($snapshot['progress'] * 100),
                'hypervisor_data'   =>  $event,
                'iaas_compute_member_id' => $computeMember->id
            ]);
        }

        if($event['operation'] == 'mod') {
            $computeMemberTask = ComputeMemberTasks::withoutGlobalScope(AuthorizationScope::class)
                ->where('hypervisor_uuid', $event['snapshot']['uuid'])
                ->first();

            $snapshot = $event['snapshot'];

            if(!$computeMemberTask) {
                $computeMemberTask = ComputeMemberTasks::create([
                    'hypervisor_uuid'   =>  $snapshot['uuid'],
                    'status'            =>  $snapshot['status'],
                    'name'              =>  $snapshot['name_label'],
                    'description'       =>  $snapshot['name_description'],
                    'error'             =>  json_encode($snapshot['error_info']),
                    'progress'          =>  ceil($snapshot['progress'] * 100),
                    'hypervisor_data'   =>  $event,
                    'iaas_compute_member_id' => $computeMember->id
                ]);
            }

            if($computeMemberTask) {
                $computeMemberTask->update([
                    'status'            =>  $event['snapshot']['status'],
                    'name'              =>  $event['snapshot']['name_label'],
                    'description'       =>  $event['snapshot']['name_description'],
                    'error'             =>  json_encode($snapshot['error_info']),
                    'progress'          =>  ceil($snapshot['progress'] * 100),
                    'hypervisor_data'   =>  $event,
                    'iaas_compute_member_id' => $computeMember->id
                ]);
            }
        }

        if($event['operation'] == 'del') {
            $computeMemberTask = ComputeMemberTasks::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_compute_member_id', $computeMember->id)
                ->where('progress', '<', 100)
                ->orderBy('created_at', 'asc')
                ->first();

            $computeMemberTask->update([
                'progress'  =>  100,
                'status'    =>  'completed',
            ]);
        }

        return [];
    }

    private function computeLeoEvents($event, $results): array {
        switch ($event['operation']) {
            case 'export_completed':

        }
    }

    private function computeVmEvents($event, $results = []) : array
    {
        $this->vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $event['snapshot']['uuid'])
            ->withTrashed()
            ->first();

        if(!$this->vm) {
            $computeMember = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
                ->where('hostname', $event['hostname'])
                ->first();

            //  There is no VM in the database, we should start the scan.
            //  This causes problems while creating new VM and or taking snapshot. We should think about something else on this.
            //  Maybe in the future we can implement a locking mechanism for this. like scan_lock = true or false
            //  self::dispatch(new ScanVirtualMachines($computeMember));
        }

        if(!$this->vm->iam_user_id) {
            Log::error( __METHOD__ . ': VM (' . $this->vm->uuid . ') does not have an associated user for event ID ' . $this->event->id);

            $account = UserHelper::getAccountById($this->vm->iam_account_id);
            if($account) {
                $this->vm->iam_user_id = UserHelper::getAccountOwner($this->vm->iam_account_id)->id;
                $this->vm->saveQuietly();
            }

            (new Communicate(UserHelper::getLeoOwner()))->sendNotification(
                subject: 'VM without user',
                message: 'The VM with UUID ' . $this->vm->uuid . ' does not have an associated user. We have' .
                ' assigned the account owner as the VM owner, but please check the VM settings to ensure' .
                ' everything is correct.');
        }

        //  Because we need the users privileges to update the VM
        UserHelper::setUserById($this->vm->iam_user_id);
        UserHelper::setCurrentAccountById($this->vm->iam_account_id);

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
                return $results;
        }

        return $results;
    }

    private function computeSrEvents($event, $results = []) : array
    {
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
                return [];
        }

        return $results;
    }

    private function computeMessageEvents($event, $results = []) : array
    {
        switch ($event['operation']) {
            case 'add':
                //  We will handle message add events here
                $results = array_merge($results, $this->messageAddOperation());
                break;
        }

        return $results;
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

    private function vmModOperation($results = [])
    {
        $event = json_decode($this->event->event, true);

        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $event['snapshot']['uuid'])
            ->withTrashed()
            ->first();

        $powerState = strtolower($event['snapshot']['power_state']);
        $ram = intval($event['snapshot']['memory_dynamic_min']) / 1024 / 1024; // Convert from bytes to MB
        $cpu = $event['snapshot']['VCPUs_max'];
        $domainType = $event['snapshot']['domain_type'];

        $currentOperation = $event['snapshot']['current_operations'];

        if($currentOperation) {
            switch($currentOperation[array_keys($currentOperation)[0]]) {
                case 'clean_reboot':
                    $powerState = 'rebooting';
                    break;
            }
        }

        if($powerState != $vm->status) {
            CommentsService::createSystemComment('Virtual machine power state changed from ' . $vm->status . ' to ' . $powerState, $vm);
            $results[] = [
                'power_state'   =>  'Changed from: ' . $vm->status . ' to ' . $powerState
            ];
        }

        if($ram != $vm->ram) {
            CommentsService::createSystemComment('Virtual machine ram changed from ' . $vm->ram . ' to ' . $ram, $vm);
            $results[] = [
                'ram'   =>  'Changed from: ' . $vm->ram . ' to ' . $ram
            ];
        }

        if($cpu != $vm->cpu) {
            CommentsService::createSystemComment('Virtual machine cpu changed from ' . $vm->cpu . ' to ' . $cpu, $vm);
            $results[] = [
                'cpu'   =>  'Changed from: ' . $vm->cpu . ' to ' . $cpu
            ];
        }

        if($domainType != $vm->domain_type) {
            CommentsService::createSystemComment('Virtual machine domain type changed from ' . $vm->domain_type . ' to ' . $domainType, $vm);
            $results[] = [
                'domain_type'   =>  'Changed from: ' . $vm->domain_type . ' to ' . $domainType
            ];
        }

        $vm->update([
            'status'    =>  $powerState,
            'ram'       =>  $ram,
            'cpu'       =>  $cpu,
            'domain_type'   =>  $domainType
        ]);

        $vm->cleanCache();

        $this->event->is_flagged = true;

        \Log::info(__METHOD__ . ': VM modified with hypervisor_uuid: ' . $event['snapshot']['uuid'] . ' for event ID ' . $this->event->id);

        return $results;
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
