<?php
namespace NextDeveloper\IAAS\Actions\NetworkPools;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAM\Database\Models\Users;

class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\NetworkPools',
        'scanned:NextDeveloper\IAAS\NetworkPools',
        'scan-failed:NextDeveloper\IAAS\NetworkPools'
    ];

    public function __construct(Datacenters $datacenters)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $datacenters;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {

    }
}
