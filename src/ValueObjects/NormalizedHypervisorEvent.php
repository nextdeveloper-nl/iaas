<?php

namespace NextDeveloper\IAAS\ValueObjects;

/**
 * The one shape every hypervisor driver's event/telemetry source gets translated into
 * before it reaches ComputeComputeMemberEventsJob. XenAPI's event_from vocabulary
 * (event class/operation, field names like power_state/memory_dynamic_min, bytes-based
 * memory units) never leaves the XenServer translator - everything downstream only
 * ever sees this.
 */
final class NormalizedHypervisorEvent
{
    public const TYPE_STARTED = 'started';
    public const TYPE_STOPPED = 'stopped';
    public const TYPE_PAUSED = 'paused';
    public const TYPE_MODIFIED = 'modified';
    public const TYPE_DELETED = 'deleted';

    public function __construct(
        /** Driver-native VM identifier: XenServer UUID, VMware MoRef, Proxmox VMID, libvirt domain UUID. */
        public readonly string $vmRef,
        /** One of self::TYPE_*. */
        public readonly string $type,
        /** Normalized field diffs - memory expressed in MB uniformly, not Xen's bytes. */
        public readonly array $changes,
        public readonly \DateTimeInterface $occurredAt,
        /** Original untranslated payload, kept for debugging - never read by downstream consumers. */
        public readonly array $raw = [],
    ) {
    }
}
