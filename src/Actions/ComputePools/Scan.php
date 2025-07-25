<?php
namespace NextDeveloper\IAAS\Actions\ComputePools;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Services\ComputePoolsService;

class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\ComputePools',
        'scanned:NextDeveloper\IAAS\ComputePools',
        'scan-failed:NextDeveloper\IAAS\ComputePools'
    ];

    public function __construct(ComputePools $pools, $params = null, $previous = null)
    {
        $this->model = $pools;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Scanning the compute members in this compute pool: ' . $this->model->name);

        $members = ComputePoolsService::getComputeMembers($this->model);

        foreach ($members as $member) {
            if($member->is_alive == false) {
                Log::warning(__METHOD__ . '| Compute member is not alive, skipping scan: ' . $member->uuid);
                continue;
            }

            dispatch(new \NextDeveloper\IAAS\Actions\ComputeMembers\Scan($member));
        }
    }
}
