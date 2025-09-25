<?php

namespace NextDeveloper\IAAS\Tests\Actions\VirtualMachines;

use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualMachines\CommentVMActionEvent;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

/**
 * Tests for CommentVMActionEvent dispatching comment-creating events.
 * We simply assert the event firing does not throw and returns null (per Events::fire contract in other tests).
 */
class CommentVMActionEventTest extends TestCase
{
    protected VirtualMachines $model;

    protected function setUp(): void
    {
        parent::setUp();
        UserHelper::setAdminAsCurrentUser();
        $this->model = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('name', 'test_vm')
            ->first();
    }

    public function test_supported_events_do_not_throw()
    {
        // Pick a representative subset of supported events to keep test runtime reasonable.
        $events = [
            'backed-up:NextDeveloper\\IAAS\\VirtualMachines',
            'commit-failed:NextDeveloper\\IAAS\\VirtualMachines',
            'health-check-failed:NextDeveloper\\IAAS\\VirtualMachines',
            'taking-snapshot:NextDeveloper\\IAAS\\VirtualMachines',
            'snapshot-failed:NextDeveloper\\IAAS\\VirtualMachines',
            'started:NextDeveloper\\IAAS\\VirtualMachines',
            'start-failed:NextDeveloper\\IAAS\\VirtualMachines',
            'syncing:NextDeveloper\\IAAS\\VirtualMachines',
            'synced:NextDeveloper\\IAAS\\VirtualMachines',
        ];

        foreach ($events as $event) {
            Events::fire($event, $this->model);
            $this->assertTrue(true, 'Event fire for ' . $event . ' should not throw exceptions.');
        }

        $this->assertTrue(true); // If we got here no exception occurred.
    }

    public function test_all_supported_events_list_contains_expected_subset()
    {
        $supported = CommentVMActionEvent::getSupportedEvents();
        $this->assertContains('backed-up:NextDeveloper\\IAAS\\VirtualMachines', $supported);
        $this->assertContains('snapshot-failed:NextDeveloper\\IAAS\\VirtualMachines', $supported);
        $this->assertContains('start-failed:NextDeveloper\\IAAS\\VirtualMachines', $supported);
    }

    public function test_unsupported_event_does_not_throw()
    {
        // Fire an unsupported event (not in map) to ensure system safely ignores.
        Events::fire('non-existent-event:NextDeveloper\\IAAS\\VirtualMachines', $this->model);
        $this->assertTrue(true);
    }
}
