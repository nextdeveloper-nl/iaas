<?php
namespace NextDeveloper\IAAS\Actions\StoragePools;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAM\Database\Models\Users;

class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\StoragePools',
        'scanned:NextDeveloper\IAAS\StoragePools',
        'scan-failed:NextDeveloper\IAAS\StoragePools'
    ];

    public function __construct(Datacenters $datacenters)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $datacenters;
        parent::__construct();
        $this->action = $this->getAction();
    }

    public function handle()
    {

    }
}
