<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputeMemberNetworkInterfacesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function device($value)
    {
        return $this->builder->where('device', 'ilike', '%' . $value . '%');
    }

        
    public function hypervisorUuid($value)
    {
        return $this->builder->where('hypervisor_uuid', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of hypervisorUuid
    public function hypervisor_uuid($value)
    {
        return $this->hypervisorUuid($value);
    }
        
    public function networkUuid($value)
    {
        return $this->builder->where('network_uuid', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of networkUuid
    public function network_uuid($value)
    {
        return $this->networkUuid($value);
    }
        
    public function networkName($value)
    {
        return $this->builder->where('network_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of networkName
    public function network_name($value)
    {
        return $this->networkName($value);
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

    
    public function isManagement($value)
    {
        return $this->builder->where('is_management', $value);
    }

        //  This is an alias function of isManagement
    public function is_management($value)
    {
        return $this->isManagement($value);
    }
     
    public function isDefault($value)
    {
        return $this->builder->where('is_default', $value);
    }

        //  This is an alias function of isDefault
    public function is_default($value)
    {
        return $this->isDefault($value);
    }
     
    public function isConnected($value)
    {
        return $this->builder->where('is_connected', $value);
    }

        //  This is an alias function of isConnected
    public function is_connected($value)
    {
        return $this->isConnected($value);
    }
     
    public function isBridge($value)
    {
        return $this->builder->where('is_bridge', $value);
    }

        //  This is an alias function of isBridge
    public function is_bridge($value)
    {
        return $this->isBridge($value);
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

    public function iaasComputeMemberId($value)
    {
            $iaasComputeMember = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('uuid', $value)->first();

        if($iaasComputeMember) {
            return $this->builder->where('iaas_compute_member_id', '=', $iaasComputeMember->id);
        }
    }

        //  This is an alias function of iaasComputeMember
    public function iaas_compute_member_id($value)
    {
        return $this->iaasComputeMember($value);
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

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE













































}
