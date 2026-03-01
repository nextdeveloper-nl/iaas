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
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function vxlan($value)
    {
        return $this->builder->where('vxlan', 'ilike', '%' . $value . '%');
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

        //  This is an alias function of speedLimit
    public function speed_limit($value)
    {
        return $this->speedLimit($value);
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

    
    public function isPublic($value)
    {
        return $this->builder->where('is_public', $value);
    }

        //  This is an alias function of isPublic
    public function is_public($value)
    {
        return $this->isPublic($value);
    }
     
    public function isVpn($value)
    {
        return $this->builder->where('is_vpn', $value);
    }

        //  This is an alias function of isVpn
    public function is_vpn($value)
    {
        return $this->isVpn($value);
    }
     
    public function isManagement($value)
    {
        return $this->builder->where('is_management', $value);
    }

        //  This is an alias function of isManagement
    public function is_management($value)
    {
        return $this->isManagement($value);
    }
     
    public function isDmz($value)
    {
        return $this->builder->where('is_dmz', $value);
    }

        //  This is an alias function of isDmz
    public function is_dmz($value)
    {
        return $this->isDmz($value);
    }
     
    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    //  This is an alias function of createdAt
    public function created_at_start($value)
    {
        return $this->createdAtStart($value);
    }

    //  This is an alias function of createdAt
    public function created_at_end($value)
    {
        return $this->createdAtEnd($value);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    //  This is an alias function of updatedAt
    public function updated_at_start($value)
    {
        return $this->updatedAtStart($value);
    }

    //  This is an alias function of updatedAt
    public function updated_at_end($value)
    {
        return $this->updatedAtEnd($value);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_start($value)
    {
        return $this->deletedAtStart($value);
    }

    //  This is an alias function of deletedAt
    public function deleted_at_end($value)
    {
        return $this->deletedAtEnd($value);
    }

    public function commonDomainId($value)
    {
            $commonDomain = \NextDeveloper\Commons\Database\Models\Domains::where('uuid', $value)->first();

        if($commonDomain) {
            return $this->builder->where('common_domain_id', '=', $commonDomain->id);
        }
    }

        //  This is an alias function of commonDomain
    public function common_domain_id($value)
    {
        return $this->commonDomain($value);
    }
    
    public function iaasDhcpServerId($value)
    {
            $iaasDhcpServer = \NextDeveloper\IAAS\Database\Models\DhcpServers::where('uuid', $value)->first();

        if($iaasDhcpServer) {
            return $this->builder->where('iaas_dhcp_server_id', '=', $iaasDhcpServer->id);
        }
    }

        //  This is an alias function of iaasDhcpServer
    public function iaas_dhcp_server_id($value)
    {
        return $this->iaasDhcpServer($value);
    }
    
    public function iaasGatewayId($value)
    {
            $iaasGateway = \NextDeveloper\IAAS\Database\Models\Gateways::where('uuid', $value)->first();

        if($iaasGateway) {
            return $this->builder->where('iaas_gateway_id', '=', $iaasGateway->id);
        }
    }

        //  This is an alias function of iaasGateway
    public function iaas_gateway_id($value)
    {
        return $this->iaasGateway($value);
    }
    
    public function iaasNetworkPoolId($value)
    {
            $iaasNetworkPool = \NextDeveloper\IAAS\Database\Models\NetworkPools::where('uuid', $value)->first();

        if($iaasNetworkPool) {
            return $this->builder->where('iaas_network_pool_id', '=', $iaasNetworkPool->id);
        }
    }

        //  This is an alias function of iaasNetworkPool
    public function iaas_network_pool_id($value)
    {
        return $this->iaasNetworkPool($value);
    }
    
    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

        //  This is an alias function of iaasCloudNode
    public function iaas_cloud_node_id($value)
    {
        return $this->iaasCloudNode($value);
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

    
    public function iaasDatacenterId($value)
    {
            $iaasDatacenter = \NextDeveloper\IAAS\Database\Models\Datacenters::where('uuid', $value)->first();

        if($iaasDatacenter) {
            return $this->builder->where('iaas_datacenter_id', '=', $iaasDatacenter->id);
        }
    }

        //  This is an alias function of iaasDatacenter
    public function iaas_datacenter_id($value)
    {
        return $this->iaasDatacenter($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE




















































}
