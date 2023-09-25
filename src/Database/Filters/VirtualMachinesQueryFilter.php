<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
use NextDeveloper\Accounts\Database\Models\User;
                    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachinesQueryFilter extends AbstractQueryFilter
{
    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function username($value)
    {
        return $this->builder->where('username', 'like', '%' . $value . '%');
    }
    
    public function password($value)
    {
        return $this->builder->where('password', 'like', '%' . $value . '%');
    }
    
    public function hostname($value)
    {
        return $this->builder->where('hostname', 'like', '%' . $value . '%');
    }
    
    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }
    
    public function notes($value)
    {
        return $this->builder->where('notes', 'like', '%' . $value . '%');
    }
    
    public function os($value)
    {
        return $this->builder->where('os', 'like', '%' . $value . '%');
    }
    
    public function distro($value)
    {
        return $this->builder->where('distro', 'like', '%' . $value . '%');
    }
    
    public function version($value)
    {
        return $this->builder->where('version', 'like', '%' . $value . '%');
    }
    
    public function features($value)
    {
        return $this->builder->where('features', 'like', '%' . $value . '%');
    }
    
    public function hypervisorUuid($value)
    {
        return $this->builder->where('hypervisor_uuid', 'like', '%' . $value . '%');
    }
    
    public function hypervisorData($value)
    {
        return $this->builder->where('hypervisor_data', 'like', '%' . $value . '%');
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
    
    public function winrmEnabled($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('winrm_enabled', $operator, $value);
    }
    
    public function isSnapshot()
    {
        return $this->builder->where('is_snapshot', true);
    }
    
    public function isLost()
    {
        return $this->builder->where('is_lost', true);
    }
    
    public function isLocked()
    {
        return $this->builder->where('is_locked', true);
    }
    
    public function lastMetadataRequestStart($date) 
    {
        return $this->builder->where('last_metadata_request', '>=', $date);
    }

    public function lastMetadataRequestEnd($date) 
    {
        return $this->builder->where('last_metadata_request', '<=', $date);
    }

    public function suspendedAtStart($date) 
    {
        return $this->builder->where('suspended_at', '>=', $date);
    }

    public function suspendedAtEnd($date) 
    {
        return $this->builder->where('suspended_at', '<=', $date);
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

    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

    public function iaasComputeMemberId($value)
    {
            $iaasComputeMember = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('uuid', $value)->first();

        if($iaasComputeMember) {
            return $this->builder->where('iaas_compute_member_id', '=', $iaasComputeMember->id);
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

    public function fromTemplateId($value)
    {
            return $this->builder->where('from_template_id', '=', $value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}