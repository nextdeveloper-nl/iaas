<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachinesManagementPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
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
        
    public function diskName($value)
    {
        return $this->builder->where('disk_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of diskName
    public function disk_name($value)
    {
        return $this->diskName($value);
    }
        
    public function diskHypervisorUuid($value)
    {
        return $this->builder->where('disk_hypervisor_uuid', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of diskHypervisorUuid
    public function disk_hypervisor_uuid($value)
    {
        return $this->diskHypervisorUuid($value);
    }
        
    public function storageVolumeName($value)
    {
        return $this->builder->where('storage_volume_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of storageVolumeName
    public function storage_volume_name($value)
    {
        return $this->storageVolumeName($value);
    }
        
    public function storageVolumeHypervisorUuid($value)
    {
        return $this->builder->where('storage_volume_hypervisor_uuid', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of storageVolumeHypervisorUuid
    public function storage_volume_hypervisor_uuid($value)
    {
        return $this->storageVolumeHypervisorUuid($value);
    }
        
    public function computeMemberName($value)
    {
        return $this->builder->where('compute_member_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of computeMemberName
    public function compute_member_name($value)
    {
        return $this->computeMemberName($value);
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
    
    public function iaasComputePoolId($value)
    {
            $iaasComputePool = \NextDeveloper\IAAS\Database\Models\ComputePools::where('uuid', $value)->first();

        if($iaasComputePool) {
            return $this->builder->where('iaas_compute_pool_id', '=', $iaasComputePool->id);
        }
    }

        //  This is an alias function of iaasComputePool
    public function iaas_compute_pool_id($value)
    {
        return $this->iaasComputePool($value);
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
