<?php
namespace NextDeveloper\IAAS\Actions\ComputePools;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAM\Database\Models\Users;

class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\ComputePools',
        'scanned:NextDeveloper\IAAS\ComputePools',
        'scan-failed:NextDeveloper\IAAS\ComputePools'
    ];

    public function __construct(Datacenters $datacenters)
    {
        trigger_error('This action is not yet implemented', E_USER_ERROR);

        $this->model = $datacenters;

        $this->queue = 'iaas';

        parent::__construct($params);
    }

    public function handle()
    {

    }
}
