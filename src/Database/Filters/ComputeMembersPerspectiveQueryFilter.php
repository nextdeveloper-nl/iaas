<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputeMembersPerspectiveQueryFilter extends AbstractQueryFilter
{
    /**
     * Filter by tags
     *
     * @param  $values
     * @return Builder
     */
    public function tags($values)
    {
        $tags = explode(',', $values);

        $search = '';

        for($i = 0; $i < count($tags); $i++) {
            $search .= "'" . trim($tags[$i]) . "',";
        }

        $search = substr($search, 0, -1);

        return $this->builder->whereRaw('tags @> ARRAY[' . $search . ']');
    }

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

        
    public function sshUsername($value)
    {
        return $this->builder->where('ssh_username', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of sshUsername
    public function ssh_username($value)
    {
        return $this->sshUsername($value);
    }
        
    public function sshPassword($value)
    {
        return $this->builder->where('ssh_password', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of sshPassword
    public function ssh_password($value)
    {
        return $this->sshPassword($value);
    }
        
    public function computePoolName($value)
    {
        return $this->builder->where('compute_pool_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of computePoolName
    public function compute_pool_name($value)
    {
        return $this->computePoolName($value);
    }
        
    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'ilike', '%' . $value . '%');
    }

        
    public function responsible($value)
    {
        return $this->builder->where('responsible', 'ilike', '%' . $value . '%');
    }

    
    public function sshPort($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ssh_port', $operator, $value);
    }

        //  This is an alias function of sshPort
    public function ssh_port($value)
    {
        return $this->sshPort($value);
    }
    
    public function totalSocket($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_socket', $operator, $value);
    }

        //  This is an alias function of totalSocket
    public function total_socket($value)
    {
        return $this->totalSocket($value);
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

        //  This is an alias function of totalCpu
    public function total_cpu($value)
    {
        return $this->totalCpu($value);
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

        //  This is an alias function of totalRam
    public function total_ram($value)
    {
        return $this->totalRam($value);
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

        //  This is an alias function of usedCpu
    public function used_cpu($value)
    {
        return $this->usedCpu($value);
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

        //  This is an alias function of usedRam
    public function used_ram($value)
    {
        return $this->usedRam($value);
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

        //  This is an alias function of freeCpu
    public function free_cpu($value)
    {
        return $this->freeCpu($value);
    }
    
    public function runningVm($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('running_vm', $operator, $value);
    }

        //  This is an alias function of runningVm
    public function running_vm($value)
    {
        return $this->runningVm($value);
    }
    
    public function haltedVm($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('halted_vm', $operator, $value);
    }

        //  This is an alias function of haltedVm
    public function halted_vm($value)
    {
        return $this->haltedVm($value);
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

        //  This is an alias function of totalVm
    public function total_vm($value)
    {
        return $this->totalVm($value);
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

        //  This is an alias function of benchmarkScore
    public function benchmark_score($value)
    {
        return $this->benchmarkScore($value);
    }
    
    public function isInMaintenance($value)
    {
        return $this->builder->where('is_in_maintenance', $value);
    }

        //  This is an alias function of isInMaintenance
    public function is_in_maintenance($value)
    {
        return $this->isInMaintenance($value);
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
     
    public function isEventServiceRunning($value)
    {
        return $this->builder->where('is_event_service_running', $value);
    }

        //  This is an alias function of isEventServiceRunning
    public function is_event_service_running($value)
    {
        return $this->isEventServiceRunning($value);
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

    public function idleTimeStart($date)
    {
        return $this->builder->where('idle_time', '>=', $date);
    }

    public function idleTimeEnd($date)
    {
        return $this->builder->where('idle_time', '<=', $date);
    }

    //  This is an alias function of idleTime
    public function idle_time_start($value)
    {
        return $this->idleTimeStart($value);
    }

    //  This is an alias function of idleTime
    public function idle_time_end($value)
    {
        return $this->idleTimeEnd($value);
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
