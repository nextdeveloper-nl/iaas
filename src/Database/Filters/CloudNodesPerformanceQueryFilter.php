<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class CloudNodesPerformanceQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function datacenterName($value)
    {
        return $this->builder->where('datacenter_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of datacenterName
    public function datacenter_name($value)
    {
        return $this->datacenterName($value);
    }
        
    public function computeVcpuHealth($value)
    {
        return $this->builder->where('compute_vcpu_health', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of computeVcpuHealth
    public function compute_vcpu_health($value)
    {
        return $this->computeVcpuHealth($value);
    }
        
    public function memoryHealth($value)
    {
        return $this->builder->where('memory_health', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of memoryHealth
    public function memory_health($value)
    {
        return $this->memoryHealth($value);
    }
        
    public function storageHealth($value)
    {
        return $this->builder->where('storage_health', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of storageHealth
    public function storage_health($value)
    {
        return $this->storageHealth($value);
    }
        
    public function networkHealth($value)
    {
        return $this->builder->where('network_health', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of networkHealth
    public function network_health($value)
    {
        return $this->networkHealth($value);
    }
    
    public function vmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vm_count', $operator, $value);
    }

        //  This is an alias function of vmCount
    public function vm_count($value)
    {
        return $this->vmCount($value);
    }
    
    public function computeVcpuTotal($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('compute_vcpu_total', $operator, $value);
    }

        //  This is an alias function of computeVcpuTotal
    public function compute_vcpu_total($value)
    {
        return $this->computeVcpuTotal($value);
    }
    
    public function computeVcpuUsed($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('compute_vcpu_used', $operator, $value);
    }

        //  This is an alias function of computeVcpuUsed
    public function compute_vcpu_used($value)
    {
        return $this->computeVcpuUsed($value);
    }
    
    public function computeAlarmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('compute_alarm_count', $operator, $value);
    }

        //  This is an alias function of computeAlarmCount
    public function compute_alarm_count($value)
    {
        return $this->computeAlarmCount($value);
    }
    
    public function storageTotalGb($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('storage_total_gb', $operator, $value);
    }

        //  This is an alias function of storageTotalGb
    public function storage_total_gb($value)
    {
        return $this->storageTotalGb($value);
    }
    
    public function storageUsedGb($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('storage_used_gb', $operator, $value);
    }

        //  This is an alias function of storageUsedGb
    public function storage_used_gb($value)
    {
        return $this->storageUsedGb($value);
    }
    
    public function storageAlarmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('storage_alarm_count', $operator, $value);
    }

        //  This is an alias function of storageAlarmCount
    public function storage_alarm_count($value)
    {
        return $this->storageAlarmCount($value);
    }
    
    public function networkAlarmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('network_alarm_count', $operator, $value);
    }

        //  This is an alias function of networkAlarmCount
    public function network_alarm_count($value)
    {
        return $this->networkAlarmCount($value);
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
     
    public function isAlive($value)
    {
        return $this->builder->where('is_alive', $value);
    }

        //  This is an alias function of isAlive
    public function is_alive($value)
    {
        return $this->isAlive($value);
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
     
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE




}
