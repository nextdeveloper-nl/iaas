<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\ValueObjects\ConsoleSession;

interface ConsoleCapableInterface
{
    /**
     * Returns everything the frontend needs to open a remote console/VNC session for
     * this VM. Every backend hands back a materially different shape (a session-token
     * URL, a WebMKS ticket, a vncproxy ticket+port, a raw VNC socket needing a
     * websockify front) - ConsoleSession normalizes that into one value object.
     */
    public function getConsoleUrl(VirtualMachines $vm): ConsoleSession;
}
