<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class KpiPerformanceQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function activeClouds($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('active_clouds', $operator, $value);
    }

        //  This is an alias function of activeClouds
    public function active_clouds($value)
    {
        return $this->activeClouds($value);
    }
    
    public function activeCloudsDelta($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('active_clouds_delta', $operator, $value);
    }

        //  This is an alias function of activeCloudsDelta
    public function active_clouds_delta($value)
    {
        return $this->activeCloudsDelta($value);
    }
    
    public function computeVcpus($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('compute_vcpus', $operator, $value);
    }

        //  This is an alias function of computeVcpus
    public function compute_vcpus($value)
    {
        return $this->computeVcpus($value);
    }
    
    public function computeVcpusDelta($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('compute_vcpus_delta', $operator, $value);
    }

        //  This is an alias function of computeVcpusDelta
    public function compute_vcpus_delta($value)
    {
        return $this->computeVcpusDelta($value);
    }
    
    public function activeTenants($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('active_tenants', $operator, $value);
    }

        //  This is an alias function of activeTenants
    public function active_tenants($value)
    {
        return $this->activeTenants($value);
    }
    
    public function activeTenantsDelta($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('active_tenants_delta', $operator, $value);
    }

        //  This is an alias function of activeTenantsDelta
    public function active_tenants_delta($value)
    {
        return $this->activeTenantsDelta($value);
    }
    
    public function alarmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_count', $operator, $value);
    }

        //  This is an alias function of alarmCount
    public function alarm_count($value)
    {
        return $this->alarmCount($value);
    }
    
    public function alarmCountDelta($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_count_delta', $operator, $value);
    }

        //  This is an alias function of alarmCountDelta
    public function alarm_count_delta($value)
    {
        return $this->alarmCountDelta($value);
    }
    
    public function alarmCriticalCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_critical_count', $operator, $value);
    }

        //  This is an alias function of alarmCriticalCount
    public function alarm_critical_count($value)
    {
        return $this->alarmCriticalCount($value);
    }
    
    public function alarmHighCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_high_count', $operator, $value);
    }

        //  This is an alias function of alarmHighCount
    public function alarm_high_count($value)
    {
        return $this->alarmHighCount($value);
    }
    
    public function alarmLowCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_low_count', $operator, $value);
    }

        //  This is an alias function of alarmLowCount
    public function alarm_low_count($value)
    {
        return $this->alarmLowCount($value);
    }
    
    public function alarmComputeMembersCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_compute_members_count', $operator, $value);
    }

        //  This is an alias function of alarmComputeMembersCount
    public function alarm_compute_members_count($value)
    {
        return $this->alarmComputeMembersCount($value);
    }
    
    public function alarmStorageMembersCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_storage_members_count', $operator, $value);
    }

        //  This is an alias function of alarmStorageMembersCount
    public function alarm_storage_members_count($value)
    {
        return $this->alarmStorageMembersCount($value);
    }
    
    public function alarmNetworkMembersCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_network_members_count', $operator, $value);
    }

        //  This is an alias function of alarmNetworkMembersCount
    public function alarm_network_members_count($value)
    {
        return $this->alarmNetworkMembersCount($value);
    }
    
    public function alarmVirtualMachinesCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('alarm_virtual_machines_count', $operator, $value);
    }

        //  This is an alias function of alarmVirtualMachinesCount
    public function alarm_virtual_machines_count($value)
    {
        return $this->alarmVirtualMachinesCount($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE






}
