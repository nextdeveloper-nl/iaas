<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\ValueObjects\NormalizedHypervisorEvent;

interface EventTranslatorCapableInterface
{
    /**
     * Translates one raw, backend-native event/telemetry payload into the normalized
     * shape ComputeComputeMemberEventsJob consumes. $rawEvent's shape is entirely
     * driver-specific (XenAPI event-message payload, vSphere PropertyCollector update,
     * polled Proxmox task-status, libvirt event callback).
     */
    public function translate(mixed $rawEvent): NormalizedHypervisorEvent;
}
