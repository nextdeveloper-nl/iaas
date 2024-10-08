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
        return $this->builder->where('hypervisor_uuid', 'like', '%' . $value . '%');
    }
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }

    public function isLocalStorage($value)
    {



        return $this->builder->where('is_local_storage', $value);
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

    public function iaasStorageMemberId($value)
    {
            $iaasStorageMember = \NextDeveloper\IAAS\Database\Models\StorageMembers::where('uuid', $value)->first();

        if($iaasStorageMember) {
            return $this->builder->where('iaas_storage_member_id', '=', $iaasStorageMember->id);
        }
    }

    public function iaasStoragePoolId($value)
    {
            $iaasStoragePool = \NextDeveloper\IAAS\Database\Models\StoragePools::where('uuid', $value)->first();

        if($iaasStoragePool) {
            return $this->builder->where('iaas_storage_pool_id', '=', $iaasStoragePool->id);
        }
    }

    public function iaasComputeMemberId($value)
    {
            $iaasComputeMember = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('uuid', $value)->first();

        if($iaasComputeMember) {
            return $this->builder->where('iaas_compute_member_id', '=', $iaasComputeMember->id);
        }
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE




























}
