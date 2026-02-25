<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StorageMemberDevicesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function deviceIdentification($value)
    {
        return $this->builder->where('device_identification', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of deviceIdentification
    public function device_identification($value)
    {
        return $this->deviceIdentification($value);
    }
        
    public function deviceType($value)
    {
        return $this->builder->where('device_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of deviceType
    public function device_type($value)
    {
        return $this->deviceType($value);
    }
    
    public function isHealthy($value)
    {
        return $this->builder->where('is_healthy', $value);
    }

        //  This is an alias function of isHealthy
    public function is_healthy($value)
    {
        return $this->isHealthy($value);
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
