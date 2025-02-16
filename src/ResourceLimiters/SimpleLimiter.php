<?php

namespace NextDeveloper\IAAS\ResourceLimiters;

use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Helpers\IaasHelper;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class SimpleLimiter extends AbstractLimiter
{
    public function __construct(\NextDeveloper\IAAS\Database\Models\Accounts $accounts)
    {
        $vms = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iam_account_id', $accounts->iam_account_id)
            ->get();

        foreach ($vms as $vm) {
            $this->cpu += $vm->cpu;
            $this->ram += $vm->ram;

            $vdis = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $vm->id)
                ->get();

            foreach ($vdis as $vdi) {
                $this->disk += $vdi->size;
            }
        }
    }

    public static function getMinimumLimits()
    {
        return config('iaas.limits.minimum');
    }

    public function hasLimitForRam($requiredRamSize)
    {
        $myLimits = IaasHelper::getLimits();

        if(!array_key_exists('simple', $myLimits))
            $myLimits = config('iaas.limits');

        $totalRamSize = $this->ram + $requiredRamSize;

        if($totalRamSize >= $myLimits['simple']['ram'])
            return false;

        return true;
    }
}
