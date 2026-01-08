<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Helpers\ResourceCalculationHelper;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractComputeMembersService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for ComputeMembers
 *
 * Class ComputeMembersService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class ComputeMembersService extends AbstractComputeMembersService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
    public static function scanLock($computeMember, $isEnabled = true)
    {
        UserHelper::runAsAdmin(function () use ($computeMember, $isEnabled) {
            $computeMember->update([
                ''
            ]);
        });
    }

    public static function addFeature($computeMember, $feature)
    {
        $features = $computeMember->features;

        if(!$features) {
            $computeMember->update([
                'features' => [$feature]
            ]);

            return $computeMember->fresh();
        }

        if(!in_array($feature, $features)) {
            $features[] = $feature;
            $computeMember->update([
                'features' => $features
            ]);
        }

        return $computeMember->fresh();
    }

    public static function removeFeature($computeMember, $feature)
    {
        $features = $computeMember->features;

        $features = array_diff($features, [$feature]);

        $computeMember->update([
            'features' => $features
        ]);
    }

    public static function getDefaultBackupRepository(ComputeMembers $member): ?Repositories
    {
        $cloud = self::getCloudNode($member);

        return Repositories::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $cloud->backup_repository_id)
            ->first();
    }

    public static function getCloudNode(ComputeMembers $computeMember) : ?CloudNodes
    {
        $computePool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $computeMember->iaas_compute_pool_id)
            ->first();

        if(!$computePool) {
            /// This is highly unlikely but we need to log this
            Log::error('[ComputeMemberService@getCloudNode] Compute pool not found for compute member: ' . $computeMember->id);

            return null;
        }

        $cloudNode = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $computePool->iaas_cloud_node_id)
            ->first();

        if(!$cloudNode) {
            /// This is highly unlikely but we need to log this
            Log::error('[ComputeMemberService@getCloudNode] Cloud node not found for compute member: ' . $computeMember->id);
        }

        return $cloudNode;
    }

    public static function getComputePool(ComputeMembers $computeMember) : ?ComputePools
    {
        return ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $computeMember->iaas_compute_pool_id)
            ->first();
    }

    public static function getNetworkPool(ComputeMembers $computeMember) : ?NetworkPools
    {
        $computePool = ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $computeMember->iaas_compute_pool_id)
            ->first();

        if(!$computePool) {
            /// This is highly unlikely but we need to log this
            Log::error('[ComputeMemberService@getNetworkPool] Compute pool not found for compute member: ' . $computeMember->id);

            return null;
        }

        $networkPool = NetworkPools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $computePool->iaas_cloud_node_id)
            ->first();

        if(!$networkPool) {
            Log::error('[ComputeMemberService@getNetworkPool] Network pool not found for compute member: ' . $computeMember->id);

            return null;
        }

        return $networkPool;
    }

    public static function hasRamResource(ComputeMembers $cm, $ram)
    {
        $ram = ResourceCalculationHelper::getActualRam($ram);

        if($cm->free_ram > $ram)
            return true;

        return false;
    }

    public static function updateStats()
    {
        /**
         * Here you will insert current resource information to stats table.
         */
    }

    public static function checkEventsService(ComputeMembers $computeMember) : bool
    {
        //  Check if the compute member is alive
        if(!$computeMember->is_alive) {
            Log::error('[ComputeMemberService@checkEventsService] Compute member is not alive: ' . $computeMember->uuid);
            return false;
        }

        //  Check if the compute member has a valid events token
        if(empty($computeMember->events_token)) {
            //  Generate an event token if it does not exist
            $computeMember->events_token = Str::random(64);
            $computeMember->save();

            $computeMember = $computeMember->fresh();
        }

        return ComputeMemberXenService::checkEventsService($computeMember);
    }

    public static function checkRrdService(ComputeMembers $computeMember, $reDeploy) : bool
    {
        //  Check if the compute member is alive
        if(!$computeMember->is_alive) {
            Log::error('[ComputeMemberService@checkEventsService] Compute member is not alive: ' . $computeMember->uuid);
            return false;
        }

        //  Check if the compute member has a valid events token
        if(empty($computeMember->events_token)) {
            //  Generate an event token if it does not exist
            $computeMember->events_token = Str::random(64);
            $computeMember->save();

            $computeMember = $computeMember->fresh();
        }

        return ComputeMemberXenService::checkRrdService($computeMember, $reDeploy);
    }

    public static function checkIpmiService(ComputeMembers $computeMember, $reDeploy) : bool
    {
        //  Check if the compute member is alive
        if(!$computeMember->is_alive) {
            Log::error('[ComputeMemberService@checkEventsService] Compute member is not alive: ' . $computeMember->uuid);
            return false;
        }

        //  Check if the compute member has a valid events token
        if(empty($computeMember->events_token)) {
            //  Generate an event token if it does not exist
            $computeMember->events_token = Str::random(64);
            $computeMember->save();

            $computeMember = $computeMember->fresh();
        }

        return ComputeMemberXenService::checkIpmiService($computeMember, $reDeploy);
    }

}
