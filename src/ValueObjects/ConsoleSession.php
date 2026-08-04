<?php

namespace NextDeveloper\IAAS\ValueObjects;

/**
 * Normalized shape for a remote console/VNC session, returned by
 * ConsoleCapableInterface::getConsoleUrl(). $protocol and $extra exist because each
 * hypervisor's console mechanism differs materially (XenServer: session-token URL;
 * VMware: WebMKS ticket; Proxmox: vncproxy/spiceproxy ticket+port; KVM: raw VNC socket
 * needing a websockify front) - $extra carries whatever backend-specific fields the
 * frontend needs beyond the URL itself (e.g. a ticket, a port, a websocket subprotocol).
 */
final class ConsoleSession
{
    public function __construct(
        public readonly string $url,
        public readonly string $protocol,
        public readonly ?\DateTimeInterface $expiresAt = null,
        public readonly array $extra = [],
    ) {
    }
}
