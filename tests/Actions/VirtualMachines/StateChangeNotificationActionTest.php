<?php

namespace NextDeveloper\IAAS\Tests\Actions\VirtualMachines;

use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

class StateChangeNotificationActionTest extends TestCase
{
    protected VirtualMachines $model;

    public function setUp(): void
    {

        parent::setUp();

        UserHelper::setAdminAsCurrentUser();

        $this->model = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('name', 'test_vm')
            ->first();
    }

    // ok
    public function test_vm_paused_event_fired()
    {
        $fire = Events::fire('paused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_pause_failed_event_fired()
    {
        $fire = Events::fire('pause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_restarted_event_fired()
    {
        $fire = Events::fire('restarted:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_restart_failed_event_fired()
    {
        $fire = Events::fire('restart-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_started_event_fired()
    {
        $fire = Events::fire('started:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_start_failed_event_fired()
    {
        $fire = Events::fire('start-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_backed_up_event_fired()
    {
        $fire = Events::fire('backed-up:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_backup_failed_event_fired()
    {
        $fire = Events::fire('backup-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    //  ok
    public function test_vm_committed_event_fired()
    {
        $fire = Events::fire('committed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_commit_failed_event_fired()
    {
        $fire = Events::fire('commit-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_health_check_failed_event_fired()
    {
        $fire = Events::fire('health-check-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_cleaned_up_event_fired()
    {
        $fire = Events::fire('cleaned-up:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_halted_event_fired()
    {
        $fire = Events::fire('halted:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_shutdown_failed_event_fired()
    {
        $fire = Events::fire('shutdown-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_unpaused_event_fired()
    {
        $fire = Events::fire('unpaused:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_unpause_failed_event_fired()
    {
        $fire = Events::fire('unpause-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_deleted_event_fired()
    {
        $fire = Events::fire('deleted:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

    // ok
    public function test_vm_delete_failed_event_fired()
    {
        $fire = Events::fire('delete-failed:NextDeveloper\IAAS\VirtualMachines', $this->model);
        $this->assertNull($fire);
    }

}
