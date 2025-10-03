<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputeMemberStorageVolumesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function hypervisorUuid($value)
    {
        return $this->builder->where('hypervisor_uuid', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of hypervisorUuid
    public function hypervisor_uuid($value)
    {
        return $this->hypervisorUuid($value);
    }
        
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function description($value)
    {
        return $this->builder->where('description', 'ilike', '%' . $value . '%');
    }

    
    public function isLocalStorage($value)
    {
        return $this->builder->where('is_local_storage', $value);
    }

        //  This is an alias function of isLocalStorage
    public function is_local_storage($value)
    {
        return $this->isLocalStorage($value);
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

    
    public function iaasStorageVolumeId($value)
    {
            $iaasStorageVolume = \NextDeveloper\IAAS\Database\Models\StorageVolumes::where('uuid', $value)->first();

        if($iaasStorageVolume) {
            return $this->builder->where('iaas_storage_volume_id', '=', $iaasStorageVolume->id);
        }
    }

        //  This is an alias function of iaasStorageVolume
    public function iaas_storage_volume_id($value)
    {
        return $this->iaasStorageVolume($value);
    }
    
    public function iaasStorageMemberId($value)
    {
            $iaasStorageMember = \NextDeveloper\IAAS\Database\Models\StorageMembers::where('uuid', $value)->first();

        if($iaasStorageMember) {
            return $this->builder->where('iaas_storage_member_id', '=', $iaasStorageMember->id);
        }
    }

        //  This is an alias function of iaasStorageMember
    public function iaas_storage_member_id($value)
    {
        return $this->iaasStorageMember($value);
    }
    
    public function iaasStoragePoolId($value)
    {
            $iaasStoragePool = \NextDeveloper\IAAS\Database\Models\StoragePools::where('uuid', $value)->first();

        if($iaasStoragePool) {
            return $this->builder->where('iaas_storage_pool_id', '=', $iaasStoragePool->id);
        }
    }

        //  This is an alias function of iaasStoragePool
    public function iaas_storage_pool_id($value)
    {
        return $this->iaasStoragePool($value);
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
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE









































}
