<?php

namespace NextDeveloper\IAAS\Tests\Integration;

use NextDeveloper\Commons\Database\GlobalScopes\AuthorizationScope;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Services\Hypervisors\VirtualMachineManager;
use NextDeveloper\IAM\Helpers\UserHelper;
use Tests\TestCase;

/**
 * Live power-state test of the agent driver on an already imported VM.
 *
 *   XEN_AGENT_TEST_VM=<virtual machine uuid>   VM made by XenAgentVmImportTest (left halted)
 *
 * Runs start, pause, resume, suspend, resume, restart, force restart, stop, force stop and checks the
 * live power state on the host after each one. The VM is left halted. Nothing is deleted.
 */
class XenAgentPowerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!env('XEN_AGENT_TEST_VM')) {
            $this->markTestSkipped('Set XEN_AGENT_TEST_VM to run the live power test.');
        }

        config(['leo.debug.ssh.dry_run' => false]);

        UserHelper::setAdminAsCurrentUser();
    }

    public function test_power_lifecycle()
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', env('XEN_AGENT_TEST_VM'))
            ->firstOrFail();

        $driver = app(VirtualMachineManager::class)->getAdapter($vm);

        $live = fn() => $driver->getHypervisorData($vm->fresh())['power-state'] ?? null;

        // A previous run can have left the VM running.
        if ($live() !== 'halted') {
            $driver->stop($vm->fresh(), true);
        }

        $this->assertSame('halted', $live(), 'The VM should start this test halted.');

        // [label, action, expected live state, needs guest tools in the VM]
        $steps = [
            ['start',          fn() => $driver->start($vm->fresh()),             'running',   false],
            ['pause',          fn() => $driver->pause($vm->fresh()),             'paused',    false],
            ['resume (unpause)', fn() => $driver->resume($vm->fresh()),          'running',   false],
            ['suspend',        fn() => $driver->suspend($vm->fresh()),           'suspended', true],
            ['resume (suspended)', fn() => $driver->resume($vm->fresh()),        'running',   true],
            ['restart',        fn() => $driver->restart($vm->fresh(), false),    'running',   true],
            ['force restart',  fn() => $driver->restart($vm->fresh(), true),     'running',   false],
            ['stop',           fn() => $driver->stop($vm->fresh(), false),       'halted',    true],
            ['start again',    fn() => $driver->start($vm->fresh()),             'running',   false],
            ['force stop',     fn() => $driver->stop($vm->fresh(), true),        'halted',    false],
            ['stop when halted', fn() => $driver->stop($vm->fresh(), true),      'halted',    false],
        ];

        $tools = false;

        foreach ($steps as [$label, $do, $expected, $needsTools]) {
            if ($needsTools) {
                // The guest tools report the OS version once they run. Wait for them (a fresh boot takes a while).
                for ($i = 0; $i < 60 && !$tools; $i++) {
                    $tools = !empty($driver->getHypervisorData($vm->fresh())['os-version']);
                    if (!$tools) {
                        sleep(3);
                    }
                }

                if (!$tools) {
                    fwrite(STDERR, "\n[power] {$label}: SKIPPED, the guest has no running Xen tools (os-version empty after 3 min)\n");
                    $this->addWarning("'{$label}' skipped: the guest has no Xen tools");

                    continue;
                }
            }

            fwrite(STDERR, "\n[power] {$label}\n");

            $do();

            // A guest without tools can take a moment to settle; look a few times.
            $state = null;
            for ($i = 0; $i < 10; $i++) {
                $state = $live();
                if ($state === $expected) {
                    break;
                }
                sleep(2);
            }

            fwrite(STDERR, "        live power-state={$state} db status=" . $vm->fresh()->status . "\n");

            $this->assertSame($expected, $state, "After '{$label}' the host reports '{$state}', expected '{$expected}'.");
        }
    }
}
