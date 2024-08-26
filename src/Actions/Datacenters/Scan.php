<?php
namespace NextDeveloper\IAAS\Actions\Datacenters;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAM\Database\Models\Users;

/**
 * This class initiates a datacenter, meaning that it will create the necessary resources for a datacenter. It includes
 * but is not limited to:
 * - Cloud Node
 * - ComputePool
 * - StoragePool
 * - NetworkPool
 */
class Scan extends AbstractAction
{
    /**
     * We are using these events here to be able to create chain reactions. And to be able to inform the
     */
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\Datacenters',
        'scanned:NextDeveloper\IAAS\Datacenters',
        'scan-failed:NextDeveloper\IAAS\Datacenters'
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
        /**
         * We will here create a Cloud Node, ComputePool, StoragePool and NetworkPool for the datacenter.
         * 1) We will create Cloud Node, with name "My Cloud Node" and type "XenServer".
         * 2) We will create ComputePool, with name "My Compute Pool".
         * 3) We will create StoragePool, with name "My Storage Pool".
         * 4) We will create NetworkPool, with name "My Network Pool".
         */
    }
}
