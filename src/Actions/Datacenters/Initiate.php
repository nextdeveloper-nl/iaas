<?php

namespace NextDeveloper\IAAS\Actions\Datacenters;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAAS\Services\NetworkPoolsService;
use NextDeveloper\IAAS\Services\StoragePoolsService;

/**
 * Class Initiate
 *
 * This class initiates a datacenter by creating the necessary resources such as Cloud Node, ComputePool,
 * StoragePool, and NetworkPool.
 *
 * @package NextDeveloper\IAAS\Actions\Datacenters
 */
class Initiate extends AbstractAction
{
    /**
     * Events triggered upon successful creation of resources.
     */
    public const EVENTS = [
        'created:NextDeveloper\IAAS\CloudNodes',
        'created:NextDeveloper\IAAS\ComputePools',
        'created:NextDeveloper\IAAS\StoragePools',
        'created:NextDeveloper\IAAS\NetworkPools'
    ];


    /**
     * Initiate constructor.
     *
     * @param Datacenters $datacenters
     */
    public function __construct(Datacenters $datacenters)
    {
        $this->model = $datacenters;
        parent::__construct();
    }

    /**
     * Handle the initiation process.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        // 1) Create Cloud Node
        $cloudNode = $this->createCloudNode();

        // 2) Create ComputePool
        $this->createComputePool($cloudNode->id);

        // 3) Create StoragePool
        $this->createStoragePool($cloudNode->id);

        // 4) Create NetworkPool
        $this->createNetworkPool($cloudNode->id);

        $this->setFinished();
    }

    /**
     * Create a Cloud Node.
     *
     * @return mixed
     * @throws \Exception
     */
    private function createCloudNode(): mixed
    {
        $cloudNode = CloudNodesService::create([
            'name'                  => 'My Cloud Node',
            'slug'                  => 'my-cloud-node',
            'iaas_datacenter_id'    => $this->model->id,
        ]);

        Events::fire('created:NextDeveloper\IAAS\CloudNodes', $cloudNode);

        $this->setProgress(25, 'Cloud Node created');

        return $cloudNode;
    }

    /**
     * Create a Compute Pool.
     *
     * @param int $cloudNodeId
     * @return void
     * @throws \Exception
     */
    private function createComputePool(int $cloudNodeId): void
    {
        $computePool = ComputePoolsService::create([
            'name'                  => 'My Compute Pool',
            'iaas_datacenter_id'    => $this->model->id,
            'iaas_cloud_node_id'    => $cloudNodeId,
        ]);

        Events::fire('created:NextDeveloper\IAAS\ComputePools', $computePool);

        $this->setProgress(50, 'Compute Pool created');
    }

    /**
     * Create a Storage Pool.
     *
     * @param int $cloudNodeId
     * @return void
     */
    private function createStoragePool(int $cloudNodeId): void
    {
        $storagePool = StoragePoolsService::create([
            'name'                  => 'My Storage Pool',
            'slug'                  => 'my-storage-pool',
            'gb_per_hour_price'     => 0.1,
            'iaas_datacenter_id'    => $this->model->id,
            'iaas_cloud_node_id'    => $cloudNodeId,
        ]);

        Events::fire('created:NextDeveloper\IAAS\StoragePools', $storagePool);

        $this->setProgress(75, 'Storage Pool created');
    }

    /**
     * Create a Network Pool.
     *
     * @param int $cloudNodeId
     * @return void
     */
    private function createNetworkPool(int $cloudNodeId): void
    {
        $networkPool = NetworkPoolsService::create([
            'name'                  => 'My Network Pool',
            'vlan_start'            => 1,
            'vlan_end'              => 10,
            'vxlan_start'           => 1,
            'vxlan_end'             => 10,
            'iaas_datacenter_id'    => $this->model->id,
            'iaas_cloud_node_id'    => $cloudNodeId,
        ]);

        Events::fire('created:NextDeveloper\IAAS\NetworkPools', $networkPool);

        $this->setProgress(100, 'Network Pool created');
    }
}
