<?php

namespace NextDeveloper\IAAS\Actions\ComputeMemberNetworkInterfaces;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

class Create extends AbstractAction
{

    public const EVENTS = [
        'creating:NextDeveloper\IAAS\ComputeMemberNetworkInterfaces',
        'created:NextDeveloper\IAAS\ComputeMemberNetworkInterfaces',
        'create-failed:NextDeveloper\IAAS\ComputeMemberNetworkInterfaces'
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

        Events::fire('initiated:NextDeveloper\IAAS\ComputeMembers', $this->model);

        $this->setProgress(100, 'Compute member initiated');
    }
}
