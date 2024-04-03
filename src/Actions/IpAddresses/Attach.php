<?php

namespace NextDeveloper\IAAS\Actions\IpAddresses;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;

/**
 * This action will scan compute member and sync all findings
 */
class Attach extends AbstractAction
{
    public const EVENTS = [
        'checked:NextDeveloper\IAAS\StorageMembers'
    ];

    public function __construct(ComputeMembers $computeMember)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $computeMember;
    }

    public function handle()
    {
        $this->setProgress(0, 'Initiate storage member started');

        Events::fire('checked:NextDeveloper\IAAS\StorageMembers', $this->model);

        $this->setProgress(100, 'Storage member scanned and synced');
    }
}
