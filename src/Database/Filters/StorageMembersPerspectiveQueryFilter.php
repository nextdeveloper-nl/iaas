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
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function hostname($value)
    {
        return $this->builder->where('hostname', 'like', '%' . $value . '%');
    }
    
    public function ipAddr($value)
    {
        return $this->builder->where('ip_addr', 'like', '%' . $value . '%');
    }
    
    public function localIpAddr($value)
    {
        return $this->builder->where('local_ip_addr', 'like', '%' . $value . '%');
    }
    
    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'like', '%' . $value . '%');
    }
    
    public function responsible($value)
    {
        return $this->builder->where('responsible', 'like', '%' . $value . '%');
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

    public function isHealthy($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_healthy', $value);
    }

    public function isMaintenance($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_maintenance', $value);
    }

    public function isAlive($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_alive', $value);
    }

    public function uptimeStart($date)
    {
        return $this->builder->where('uptime', '>=', $date);
    }

    public function uptimeEnd($date)
    {
        return $this->builder->where('uptime', '<=', $date);
    }

    public function iaasStoragePoolId($value)
    {
            $iaasStoragePool = \NextDeveloper\IAAS\Database\Models\StoragePools::where('uuid', $value)->first();

        if($iaasStoragePool) {
            return $this->builder->where('iaas_storage_pool_id', '=', $iaasStoragePool->id);
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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
