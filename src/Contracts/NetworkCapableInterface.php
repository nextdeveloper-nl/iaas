<?php

namespace NextDeveloper\IAAS\Contracts;

use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;

interface NetworkCapableInterface
{
    public function createNetworkCard(VirtualMachines $vm, string $networkUuid, int $device): VirtualNetworkCards;

    public function destroyNetworkCard(VirtualNetworkCards $vif): bool;

    public function syncNetworkCard(VirtualNetworkCards $vif): VirtualNetworkCards;

    /**
     * Applies the vif's ip allow-list (VirtualNetworkCards.ip filtering) to the backend.
     */
    public function applyIpFilter(VirtualNetworkCards $vif): bool;

    /**
     * Sets the intent-level filter mode; each driver maps this to its own native
     * vocabulary (XenServer's locking-mode: locked/unlocked/disabled/network_default).
     */
    public function setIpFilterMode(VirtualNetworkCards $vif, string $mode): bool;
}
