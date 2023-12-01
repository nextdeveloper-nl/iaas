<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
use NextDeveloper\Accounts\Database\Models\User;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputeMembersQueryFilter extends AbstractQueryFilter
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
    
    public function apiUrl($value)
    {
        return $this->builder->where('api_url', 'like', '%' . $value . '%');
    }
    
    public function username($value)
    {
        return $this->builder->where('username', 'like', '%' . $value . '%');
    }
    
    public function password($value)
    {
        return $this->builder->where('password', 'like', '%' . $value . '%');
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

    public function port($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('port', $operator, $value);
    }
    
    public function totalCpu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_cpu', $operator, $value);
    }
    
    public function totalRam($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_ram', $operator, $value);
    }
    
    public function usedCpu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('used_cpu', $operator, $value);
    }
    
    public function usedRam($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('used_ram', $operator, $value);
    }
    
    public function freeCpu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('free_cpu', $operator, $value);
    }
    
    public function freeRam($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('free_ram', $operator, $value);
    }
    
    public function totalVm($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_vm', $operator, $value);
    }
    
    public function overbookingRatio($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('overbooking_ratio', $operator, $value);
    }
    
    public function maxOverbookingRatio($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('max_overbooking_ratio', $operator, $value);
    }
    
    public function benchmarkScore($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('benchmark_score', $operator, $value);
    }
    
    public function isBehindFirewall()
    {
        return $this->builder->where('is_behind_firewall', true);
    }
    
    public function isMaintenance()
    {
        return $this->builder->where('is_maintenance', true);
    }
    
    public function isAlive()
    {
        return $this->builder->where('is_alive', true);
    }
    
    public function uptimeStart($date) 
    {
        return $this->builder->where('uptime', '>=', $date);
    }

    public function uptimeEnd($date) 
    {
        return $this->builder->where('uptime', '<=', $date);
    }

    public function idleTimeStart($date) 
    {
        return $this->builder->where('idle_time', '>=', $date);
    }

    public function idleTimeEnd($date) 
    {
        return $this->builder->where('idle_time', '<=', $date);
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

    public function iaasComputePoolId($value)
    {
            $iaasComputePool = \NextDeveloper\IAAS\Database\Models\ComputePools::where('uuid', $value)->first();

        if($iaasComputePool) {
            return $this->builder->where('iaas_compute_pool_id', '=', $iaasComputePool->id);
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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
}