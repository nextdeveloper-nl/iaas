<?php
namespace NextDeveloper\IAAS\Actions\Datacenters;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class initiates a datacenter, meaning that it will create the necessary resources for a datacenter. It includes
 * but is not limited to:
 * - Cloud Node
 * - ComputePool
 * - StoragePool
 * - NetworkPool
 */
class UpdateResources extends AbstractAction
{
    /**
     * We are using these events here to be able to create chain reactions. And to be able to inform the
     */
    public const EVENTS = [
        'updating-resources:NextDeveloper\IAAS\ComputeMembers',
        'resources-updated:NextDeveloper\IAAS\ComputeMembers',
    ];

    public function __construct(Datacenters $datacenters, $params = null, $previousAction = null)
    {
        $this->model = $datacenters;

        $this->queue = 'iaas';

        parent::__construct($params, $previousAction);
    }

    public function handle()
    {
        $this->setProgress(0, 'Updating datacenter resources started');

        $cloudNodes = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_datacenter_id', $this->model->id)
            ->get();

        if(!$cloudNodes) {
            $this->setFinished('No cloud nodes found for datacenter');
            return;
        }

        foreach ($cloudNodes as $node) {
            $this->setProgress(50, 'Updating cloud node resources: ' . $node->name);
            $computePools = ComputePools::withoutGlobalScope(AuthorizationScope::class)
                ->withoutGlobalScope(LimitScope::class)
                ->where('iaas_cloud_node_id', $node->id)
                ->get();

            foreach ($computePools as $pool) {
                $this->setProgress(50, 'Updating cloud pool resources: ' . $pool->name);
                $computeMembers = ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
                    ->withoutGlobalScope(LimitScope::class)
                    ->where('iaas_compute_pool_id', $pool->id)
                    ->get();

                foreach ($computeMembers as $member) {
                    $this->setProgress(50, 'Updating compute member resources: ' . $member->name);
                    dispatch((new \NextDeveloper\IAAS\Actions\ComputeMembers\UpdateResources($member, $this->params, $this))->runAsAdministrator());
                }
            }
        }

        $this->setFinished('Updated datacenter resources');
    }
}
