<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
use NextDeveloper\Accounts\Database\Models\User;
                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputePoolsQueryFilter extends AbstractQueryFilter
{
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
    
    public function virtualizationVersion($value)
    {
        return $this->builder->where('virtualization_version', 'like', '%' . $value . '%');
    }
    
    public function provisioningAlg($value)
    {
        return $this->builder->where('provisioning_alg', 'like', '%' . $value . '%');
    }
    
    public function managementPackageName($value)
    {
        return $this->builder->where('management_package_name', 'like', '%' . $value . '%');
    }

    public function isActive()
    {
        return $this->builder->where('is_active', true);
    }
    
    public function isAlive()
    {
        return $this->builder->where('is_alive', true);
    }
    
    public function isPublic()
    {
        return $this->builder->where('is_public', true);
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

    public function iaasDatacenterId($value)
    {
            $iaasDatacenter = \NextDeveloper\IAAS\Database\Models\Datacenters::where('uuid', $value)->first();

        if($iaasDatacenter) {
            return $this->builder->where('iaas_datacenter_id', '=', $iaasDatacenter->id);
        }
    }

    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}