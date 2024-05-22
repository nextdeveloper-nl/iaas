<?php

namespace NextDeveloper\IAAS\Actions\Datacenters;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Services\CloudNodesService;
use NextDeveloper\IAAS\Services\ComputePoolsService;
use NextDeveloper\IAAS\Services\NetworkPoolsService;
use NextDeveloper\IAAS\Services\StoragePoolsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

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
        'created:NextDeveloper\IAAS\NetworkPools',
        'initiation-failed:NextDeveloper\IAAS\Datacenters',
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
        try {
            $this->setProgress(0, 'Initiating Datacenter');
            // 1) Create Cloud Node
            $cloudNode = $this->createCloudNode();

            $this->setProgress(25, 'Cloud Node created');
            // 2) Create ComputePool
            $this->createComputePool($cloudNode);

            $this->setProgress(50, 'Compute pool created');
            // 3) Create StoragePool
            $this->createStoragePool($cloudNode);

            $this->setProgress(75, 'Storage pool created');
            // 4) Create NetworkPool
            $this->createNetworkPool($cloudNode);

            $this->setProgress(90, 'Network pool created');

            $this->setFinished('Datacenter initiated successfully');
        }
        catch (\Exception $e) {
            Events::fire('initiation-failed:NextDeveloper\IAAS\Datacenters', $this->model);
            throw $e;
        }
    }

    /**
     * Create a Cloud Node.
     *
     * @return mixed
     * @throws \Exception
     */
    private function createCloudNode(): mixed
    {
        $cloudNode = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_datacenter_id', $this->model->id)
            ->first();

        if($cloudNode)
            return $cloudNode;

        $cloudNode = CloudNodesService::create([
            'name'                  => $this->model->name . ' Cloud Node',
            'slug'                  => 'my-cloud-node',
            'iaas_datacenter_id'    => $this->model->id,
        ]);

        Events::fire('created:NextDeveloper\IAAS\CloudNodes', $cloudNode);

        return $cloudNode;
    }

    /**
     * Create a Compute Pool.
     *
     * @param $cloudNode
     * @return void
     * @throws \Exception
     */
    private function createComputePool($cloudNode): mixed
    {
        $computePool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $cloudNode->id)
            ->where('iaas_datacenter_id', $this->model->id)
            ->first();

        if($computePool)
            return $computePool;

        $computePool = ComputePoolsService::create([
            'name'                  => $this->model->name . ' Compute Pool',
            'iaas_datacenter_id'    => $this->model->id,
            'iaas_cloud_node_id'    => $cloudNode->id,
        ]);

        Events::fire('created:NextDeveloper\IAAS\ComputePools', $computePool);
    }

    /**
     * Create a Storage Pool.
     *
     * @param $cloudNode
     * @return void
     */
    private function createStoragePool($cloudNode): mixed
    {
        $storagePool = StoragePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $cloudNode->id)
            ->where('iaas_datacenter_id', $this->model->id)
            ->first();

        if($storagePool)
            return $storagePool;

        $storagePool = StoragePoolsService::create([
            'name'                  => $this->model->name . ' Storage Pool',
            'slug'                  => 'my-storage-pool',
            'gb_per_hour_price'     => 0.1,
            'iaas_datacenter_id'    => $this->model->id,
            'iaas_cloud_node_id'    => $cloudNode->id,
        ]);

        Events::fire('created:NextDeveloper\IAAS\StoragePools', $storagePool);
    }

    /**
     * Create a Network Pool.
     *
     * @param $cloudNode
     * @return void
     */
    private function createNetworkPool($cloudNode): mixed
    {
        $networkPool = NetworkPools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $cloudNode->id)
            ->where('iaas_datacenter_id', $this->model->id)
            ->first();

        if($networkPool)
            return $networkPool;

        $networkPool = NetworkPoolsService::create([
            'name'                  => $this->model->name . ' Network Pool',
            'vlan_start'            => 1,
            'vlan_end'              => 10,
            'vxlan_start'           => 1,
            'vxlan_end'             => 10,
            'iaas_datacenter_id'    => $this->model->id,
            'iaas_cloud_node_id'    => $cloudNode->id,
        ]);

        Events::fire('created:NextDeveloper\IAAS\NetworkPools', $networkPool);
    }
}
