<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class NetworksQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function vxlan($value)
    {
        return $this->builder->where('vxlan', 'like', '%' . $value . '%');
    }

    public function vlan($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vlan', $operator, $value);
    }

    public function bandwidth($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('bandwidth', $operator, $value);
    }

    public function mtu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('mtu', $operator, $value);
    }

    public function speedLimit($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('speed_limit', $operator, $value);
    }

    public function isPublic($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_public', $value);
    }

    public function isVpn($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_vpn', $value);
    }

    public function isManagement($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_management', $value);
    }

    public function isDmz($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_dmz', $value);
    }

    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    public function commonDomainId($value)
    {
            $commonDomain = \NextDeveloper\Commons\Database\Models\Domains::where('uuid', $value)->first();

        if($commonDomain) {
            return $this->builder->where('common_domain_id', '=', $commonDomain->id);
        }
    }

    public function iaasDhcpServerId($value)
    {
            $iaasDhcpServer = \NextDeveloper\IAAS\Database\Models\DhcpServers::where('uuid', $value)->first();

        if($iaasDhcpServer) {
            return $this->builder->where('iaas_dhcp_server_id', '=', $iaasDhcpServer->id);
        }
    }

    public function iaasGatewayId($value)
    {
            $iaasGateway = \NextDeveloper\IAAS\Database\Models\Gateways::where('uuid', $value)->first();

        if($iaasGateway) {
            return $this->builder->where('iaas_gateway_id', '=', $iaasGateway->id);
        }
    }

    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    public function iamUserId($value)
    {
            $iamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($iamUser) {
            return $this->builder->where('iam_user_id', '=', $iamUser->id);
        }
    }

    public function iaasNetworkPoolId($value)
    {
            $iaasNetworkPool = \NextDeveloper\IAAS\Database\Models\NetworkPools::where('uuid', $value)->first();

        if($iaasNetworkPool) {
            return $this->builder->where('iaas_network_pool_id', '=', $iaasNetworkPool->id);
        }
    }

    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE













}
