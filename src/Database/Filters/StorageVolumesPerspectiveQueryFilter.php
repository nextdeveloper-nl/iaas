<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StorageVolumesPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }


    public function diskPhysicalType($value)
    {
        return $this->builder->where('disk_physical_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of diskPhysicalType
    public function disk_physical_type($value)
    {
        return $this->diskPhysicalType($value);
    }

    public function storagePool($value)
    {
        return $this->builder->where('storage_pool', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of storagePool
    public function storage_pool($value)
    {
        return $this->storagePool($value);
    }

    public function storageMember($value)
    {
        return $this->builder->where('storage_member', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of storageMember
    public function storage_member($value)
    {
        return $this->storageMember($value);
    }

    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'ilike', '%' . $value . '%');
    }


    public function responsible($value)
    {
        return $this->builder->where('responsible', 'ilike', '%' . $value . '%');
    }


    public function totalHdd($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_hdd', $operator, $value);
    }

        //  This is an alias function of totalHdd
    public function total_hdd($value)
    {
        return $this->totalHdd($value);
    }

    public function freeHdd($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('free_hdd', $operator, $value);
    }

        //  This is an alias function of freeHdd
    public function free_hdd($value)
    {
        return $this->freeHdd($value);
    }

    public function virtualAllocation($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('virtual_allocation', $operator, $value);
    }

        //  This is an alias function of virtualAllocation
    public function virtual_allocation($value)
    {
        return $this->virtualAllocation($value);
    }

    public function isStorage($value)
    {
        return $this->builder->where('is_storage', $value);
    }

        //  This is an alias function of isStorage
    public function is_storage($value)
    {
        return $this->isStorage($value);
    }

    public function isRepo($value)
    {
        return $this->builder->where('is_repo', $value);
    }

        //  This is an alias function of isRepo
    public function is_repo($value)
    {
        return $this->isRepo($value);
    }

    public function isCdrom($value)
    {
        return $this->builder->where('is_cdrom', $value);
    }

        //  This is an alias function of isCdrom
    public function is_cdrom($value)
    {
        return $this->isCdrom($value);
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
