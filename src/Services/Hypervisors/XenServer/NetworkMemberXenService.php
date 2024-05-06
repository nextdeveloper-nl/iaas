<?php

namespace NextDeveloper\IAAS\Services\Hypervisors\XenServer;

use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Database\Models\NetworkPools;
use NextDeveloper\IAAS\Services\ComputeMembersService;
use NextDeveloper\IAAS\Services\NetworkMembersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class NetworkMemberXenService
{
    public static function createNetworkMemberFromComputeMember(ComputeMembers $computeMember)
    {
        $networkPool = self::getNetworkPoolFromComputeMember($computeMember);
    }

    public static function getNetworkPoolFromComputeMember(ComputeMembers $computeMember)
    {
        $cloudNode = ComputeMembersService::getCloudNode($computeMember);

        $networkPool = NetworkPools::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_cloud_node_id', $cloudNode->id)
            ->first();

        $networkMember = NetworkMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('local_ip_addr', $computeMember->local_ip_addr)
            ->first();

        if(!$networkMember) {
            $networkMember = NetworkMembersService::create([
                'name'  =>  'Open vSwitch On ' . $computeMember->name,
                'ip_addr'   =>  $computeMember->ip_addr,
                'local_ip_addr' =>  $computeMember->local_ip_addr,
                'ssh_username'  =>  $computeMember->ssh_username,
                'ssh_password'  =>  decrypt($computeMember->ssh_password),
                'ssh_port'  =>  $computeMember->ssh_port,
                'iaas_network_pool_id'  =>  $networkPool->id,
                'is_management_agent_available'  =>  false,
                'is_behind_firewall'    =>  $computeMember->is_behind_firewall,
                'iam_account_id'    =>  $computeMember->iam_account_id,
                'iam_user_id'   =>  $computeMember->iam_user_id
            ]);
        }

        return $networkMember;
    }
}
