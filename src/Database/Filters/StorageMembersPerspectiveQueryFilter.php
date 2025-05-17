<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StorageMembersPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }


    public function hostname($value)
    {
        return $this->builder->where('hostname', 'ilike', '%' . $value . '%');
    }


    public function ipAddr($value)
    {
        return $this->builder->where('ip_addr', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of ipAddr
    public function ip_addr($value)
    {
        return $this->ipAddr($value);
    }

    public function localIpAddr($value)
    {
        return $this->builder->where('local_ip_addr', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of localIpAddr
    public function local_ip_addr($value)
    {
        return $this->localIpAddr($value);
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

    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'ilike', '%' . $value . '%');
    }


    public function responsible($value)
    {
        return $this->builder->where('responsible', 'ilike', '%' . $value . '%');
    }


    public function totalDisk($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_disk', $operator, $value);
    }

        //  This is an alias function of totalDisk
    public function total_disk($value)
    {
        return $this->totalDisk($value);
    }

    public function usedDisk($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('used_disk', $operator, $value);
    }

        //  This is an alias function of usedDisk
    public function used_disk($value)
    {
        return $this->usedDisk($value);
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

    public function isMaintenance($value)
    {
        return $this->builder->where('is_maintenance', $value);
    }

        //  This is an alias function of isMaintenance
    public function is_maintenance($value)
    {
        return $this->isMaintenance($value);
    }

    public function isAlive($value)
    {
        return $this->builder->where('is_alive', $value);
    }

        //  This is an alias function of isAlive
    public function is_alive($value)
    {
        return $this->isAlive($value);
    }

    public function uptimeStart($date)
    {
        return $this->builder->where('uptime', '>=', $date);
    }

    public function uptimeEnd($date)
    {
        return $this->builder->where('uptime', '<=', $date);
    }

    //  This is an alias function of uptime
    public function uptime_start($value)
    {
        return $this->uptimeStart($value);
    }

    //  This is an alias function of uptime
    public function uptime_end($value)
    {
        return $this->uptimeEnd($value);
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
