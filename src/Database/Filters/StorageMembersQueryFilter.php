<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class StorageMembersQueryFilter extends AbstractQueryFilter
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
        
    public function configurationData($value)
    {
        return $this->builder->where('configuration_data', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of configurationData
    public function configuration_data($value)
    {
        return $this->configurationData($value);
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
    
    public function isHealthy($value)
    {
        return $this->builder->where('is_healthy', $value);
    }

        //  This is an alias function of isHealthy
    public function is_healthy($value)
    {
        return $this->isHealthy($value);
    }
     
    public function isBehindFirewall($value)
    {
        return $this->builder->where('is_behind_firewall', $value);
    }

        //  This is an alias function of isBehindFirewall
    public function is_behind_firewall($value)
    {
        return $this->isBehindFirewall($value);
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
