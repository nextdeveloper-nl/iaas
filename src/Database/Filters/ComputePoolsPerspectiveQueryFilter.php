<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputePoolsPerspectiveQueryFilter extends AbstractQueryFilter
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
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }

        
    public function virtualization($value)
    {
        return $this->builder->where('virtualization', 'like', '%' . $value . '%');
    }

        
    public function resourceValidator($value)
    {
        return $this->builder->where('resource_validator', 'like', '%' . $value . '%');
    }

        //  This is an alias function of resourceValidator
    public function resource_validator($value)
    {
        return $this->resourceValidator($value);
    }
        
    public function currency($value)
    {
        return $this->builder->where('currency', 'like', '%' . $value . '%');
    }

        
    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'like', '%' . $value . '%');
    }

        
    public function responsible($value)
    {
        return $this->builder->where('responsible', 'like', '%' . $value . '%');
    }

        
    public function poolType($value)
    {
        return $this->builder->where('pool_type', 'like', '%' . $value . '%');
    }

        //  This is an alias function of poolType
    public function pool_type($value)
    {
        return $this->poolType($value);
    }
    
    public function totalRamInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_ram_in_pool', $operator, $value);
    }

        //  This is an alias function of totalRamInPool
    public function total_ram_in_pool($value)
    {
        return $this->totalRamInPool($value);
    }
    
    public function totalCpuInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_cpu_in_pool', $operator, $value);
    }

        //  This is an alias function of totalCpuInPool
    public function total_cpu_in_pool($value)
    {
        return $this->totalCpuInPool($value);
    }
    
    public function usedRamInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('used_ram_in_pool', $operator, $value);
    }

        //  This is an alias function of usedRamInPool
    public function used_ram_in_pool($value)
    {
        return $this->usedRamInPool($value);
    }
    
    public function usedCpuInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('used_cpu_in_pool', $operator, $value);
    }

        //  This is an alias function of usedCpuInPool
    public function used_cpu_in_pool($value)
    {
        return $this->usedCpuInPool($value);
    }
    
    public function totalVmInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_vm_in_pool', $operator, $value);
    }

        //  This is an alias function of totalVmInPool
    public function total_vm_in_pool($value)
    {
        return $this->totalVmInPool($value);
    }
    
    public function runningRamInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('running_ram_in_pool', $operator, $value);
    }

        //  This is an alias function of runningRamInPool
    public function running_ram_in_pool($value)
    {
        return $this->runningRamInPool($value);
    }
    
    public function haltedRamInPool($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('halted_ram_in_pool', $operator, $value);
    }

        //  This is an alias function of haltedRamInPool
    public function halted_ram_in_pool($value)
    {
        return $this->haltedRamInPool($value);
    }
    
    public function isActive($value)
    {
        return $this->builder->where('is_active', $value);
    }

        //  This is an alias function of isActive
    public function is_active($value)
    {
        return $this->isActive($value);
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

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

























}
