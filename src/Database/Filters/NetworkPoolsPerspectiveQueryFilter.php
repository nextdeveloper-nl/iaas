<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class NetworkPoolsPerspectiveQueryFilter extends AbstractQueryFilter
{
    /**
     * Filter by tags
     *
     * @param  $values
     * @return Builder
     */
    public function tags($values)
    {
        $tags = explode(',', $values);

        $search = '';

        for($i = 0; $i < count($tags); $i++) {
            $search .= "'" . trim($tags[$i]) . "',";
        }

        $search = substr($search, 0, -1);

        return $this->builder->whereRaw('tags @> ARRAY[' . $search . ']');
    }

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }

        
    public function resourceValidator($value)
    {
        return $this->builder->where('resource_validator', 'like', '%' . $value . '%');
    }

        //  This is an alias function of resourceValidator
    public function resource_validator($value)
    {
        return $this->resourceValidator($value);
    }
        
    public function provisioningAlg($value)
    {
        return $this->builder->where('provisioning_alg', 'like', '%' . $value . '%');
    }

        //  This is an alias function of provisioningAlg
    public function provisioning_alg($value)
    {
        return $this->provisioningAlg($value);
    }
        
    public function currency($value)
    {
        return $this->builder->where('currency', 'like', '%' . $value . '%');
    }

        
    public function datacenter($value)
    {
        return $this->builder->where('datacenter', 'like', '%' . $value . '%');
    }

        
    public function cloudNode($value)
    {
        return $this->builder->where('cloud_node', 'like', '%' . $value . '%');
    }

        //  This is an alias function of cloudNode
    public function cloud_node($value)
    {
        return $this->cloudNode($value);
    }
        
    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'like', '%' . $value . '%');
    }

        
    public function responsible($value)
    {
        return $this->builder->where('responsible', 'like', '%' . $value . '%');
    }

    
    public function vlanStart($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vlan_start', $operator, $value);
    }

        //  This is an alias function of vlanStart
    public function vlan_start($value)
    {
        return $this->vlanStart($value);
    }
    
    public function vlanEnd($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vlan_end', $operator, $value);
    }

        //  This is an alias function of vlanEnd
    public function vlan_end($value)
    {
        return $this->vlanEnd($value);
    }
    
    public function vxlanStart($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vxlan_start', $operator, $value);
    }

        //  This is an alias function of vxlanStart
    public function vxlan_start($value)
    {
        return $this->vxlanStart($value);
    }
    
    public function vxlanEnd($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vxlan_end', $operator, $value);
    }

        //  This is an alias function of vxlanEnd
    public function vxlan_end($value)
    {
        return $this->vxlanEnd($value);
    }
    
    public function totalNetworks($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_networks', $operator, $value);
    }

        //  This is an alias function of totalNetworks
    public function total_networks($value)
    {
        return $this->totalNetworks($value);
    }
    
    public function isActive($value)
    {
        return $this->builder->where('is_active', $value);
    }

        //  This is an alias function of isActive
    public function is_active($value)
    {
        return $this->isActive($value);
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
