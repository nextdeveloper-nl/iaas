<?php
namespace NextDeveloper\IAAS\Actions\ComputePools;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\ComputeMembers\ScanVirtualMachines as ScanComputeMemberVMs;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class ScanVirtualMachines extends AbstractAction
{
    public const EVENTS = [
        'scanning-virtual-machines:NextDeveloper\IAAS\ComputePools',
        'virtual-machines-scanned:NextDeveloper\IAAS\ComputePools',
        'virtual-machine-scan-failed:NextDeveloper\IAAS\ComputePools'
    ];

    public function __construct(ComputePools $pool)
    {
        $this->model = $pool;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Compute pool scan started');
        Events::fire('scanning-virtual-machines:NextDeveloper\IAAS\ComputePools', $this->model);

        $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_compute_pool_id', $this->model->id)
            ->get();

        foreach ($computeMembers as $member) {
            dispatch(new ScanComputeMemberVMs($member))->onQueue($this->queue);
        }
    }
}
