<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class CustomerResourcesPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function accountName($value)
    {
        return $this->builder->where('account_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of accountName
    public function account_name($value)
    {
        return $this->accountName($value);
    }
        
    public function userName($value)
    {
        return $this->builder->where('user_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of userName
    public function user_name($value)
    {
        return $this->userName($value);
    }
        
    public function userEmail($value)
    {
        return $this->builder->where('user_email', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of userEmail
    public function user_email($value)
    {
        return $this->userEmail($value);
    }
        
    public function resourceType($value)
    {
        return $this->builder->where('resource_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of resourceType
    public function resource_type($value)
    {
        return $this->resourceType($value);
    }
        
    public function resourceName($value)
    {
        return $this->builder->where('resource_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of resourceName
    public function resource_name($value)
    {
        return $this->resourceName($value);
    }
        
    public function resourceStatus($value)
    {
        return $this->builder->where('resource_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of resourceStatus
    public function resource_status($value)
    {
        return $this->resourceStatus($value);
    }
        
    public function hypervisorNameLabel($value)
    {
        return $this->builder->where('hypervisor_name_label', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of hypervisorNameLabel
    public function hypervisor_name_label($value)
    {
        return $this->hypervisorNameLabel($value);
    }
    
    public function cpu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('cpu', $operator, $value);
    }

    
    public function ram($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ram', $operator, $value);
    }

    
    public function isAccountSuspended($value)
    {
        return $this->builder->where('is_account_suspended', $value);
    }

        //  This is an alias function of isAccountSuspended
    public function is_account_suspended($value)
    {
        return $this->isAccountSuspended($value);
    }
     
    public function isCrmSuspended($value)
    {
        return $this->builder->where('is_crm_suspended', $value);
    }

        //  This is an alias function of isCrmSuspended
    public function is_crm_suspended($value)
    {
        return $this->isCrmSuspended($value);
    }
     
    public function isCrmDisabled($value)
    {
        return $this->builder->where('is_crm_disabled', $value);
    }

        //  This is an alias function of isCrmDisabled
    public function is_crm_disabled($value)
    {
        return $this->isCrmDisabled($value);
    }
     
    public function isAccountingDisabled($value)
    {
        return $this->builder->where('is_accounting_disabled', $value);
    }

        //  This is an alias function of isAccountingDisabled
    public function is_accounting_disabled($value)
    {
        return $this->isAccountingDisabled($value);
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

    
    public function resourceId($value)
    {
            $resource = \NextDeveloper\\Database\Models\Resources::where('uuid', $value)->first();

        if($resource) {
            return $this->builder->where('resource_id', '=', $resource->id);
        }
    }

        //  This is an alias function of resource
    public function resource_id($value)
    {
        return $this->resource($value);
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
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
