<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer\Events;

use NextDeveloper\IAAS\ValueObjects\NormalizedHypervisorEvent;

/**
 * Translates a raw XenAPI `vm` class event (as delivered by events.py's event_from
 * long-poll, see ComputeComputeMemberEventsJob) into NormalizedHypervisorEvent. This is
 * the one place XenAPI's event vocabulary (operation add/mod/del, snapshot field names
 * like power_state/memory_dynamic_min/VCPUs_max/domain_type, bytes-based memory units)
 * is read - everything downstream of the translator only ever sees the normalized shape.
 *
 * Scope note: this only covers `vm` class events (VM lifecycle/state). XenAPI's other
 * event classes (message/sr/task/leo, handled separately in ComputeComputeMemberEventsJob)
 * are host/task-level telemetry that doesn't map onto a VM-centric normalized event and
 * is out of scope here - see docs/hypervisor-driver-architecture.md §5.
 */
class XenServerEventTranslator
{
    public static function translate(mixed $rawEvent): NormalizedHypervisorEvent
    {
        $snapshot = $rawEvent['snapshot'] ?? [];
        $operation = $rawEvent['operation'] ?? null;
        $vmRef = $snapshot['uuid'] ?? ($rawEvent['ref'] ?? '');

        if ($operation === 'del') {
            return new NormalizedHypervisorEvent(
                vmRef: $vmRef,
                type: NormalizedHypervisorEvent::TYPE_DELETED,
                changes: [],
                occurredAt: now()->toDateTimeImmutable(),
                raw: is_array($rawEvent) ? $rawEvent : [],
            );
        }

        $powerState = strtolower($snapshot['power_state'] ?? '');

        //  XenAPI reports "running" for a VM mid-reboot too - current_operations is the
        //  only way to tell a genuine transient "rebooting" state apart from "running".
        $currentOperations = $snapshot['current_operations'] ?? [];

        if ($currentOperations) {
            $firstOperation = $currentOperations[array_key_first($currentOperations)] ?? null;

            if ($firstOperation === 'clean_reboot') {
                $powerState = 'rebooting';
            }
        }

        $type = match ($powerState) {
            'running' => NormalizedHypervisorEvent::TYPE_STARTED,
            'halted' => NormalizedHypervisorEvent::TYPE_STOPPED,
            'paused', 'suspended' => NormalizedHypervisorEvent::TYPE_PAUSED,
            default => NormalizedHypervisorEvent::TYPE_MODIFIED,
        };

        $changes = array_filter([
            'power_state'   =>  $powerState !== '' ? $powerState : null,
            'ram_mb'        =>  isset($snapshot['memory_dynamic_min']) ? (int) ($snapshot['memory_dynamic_min'] / 1024 / 1024) : null,
            'cpu'           =>  $snapshot['VCPUs_max'] ?? null,
            'domain_type'   =>  $snapshot['domain_type'] ?? null,
        ], fn ($value) => $value !== null);

        return new NormalizedHypervisorEvent(
            vmRef: $vmRef,
            type: $type,
            changes: $changes,
            occurredAt: now()->toDateTimeImmutable(),
            raw: is_array($rawEvent) ? $rawEvent : [],
        );
    }
}
