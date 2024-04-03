<?php

namespace NextDeveloper\IAAS\Actions\ComputeMembers;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * This action will scan compute member and sync all findings
 */
class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanned:NextDeveloper\IAAS\ComputeMembers'
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $computeMember;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate compute member started');

        $this->model->status = 'initiated';
        $this->model->save();

        Events::fire('scanned:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member scanned and synced');
    }
}
