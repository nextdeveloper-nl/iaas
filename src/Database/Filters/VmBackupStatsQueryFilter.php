<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VmBackupStatsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function vmsProtected($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vms_protected', $operator, $value);
    }

        //  This is an alias function of vmsProtected
    public function vms_protected($value)
    {
        return $this->vmsProtected($value);
    }
    
    public function vmsProtectedDelta($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vms_protected_delta', $operator, $value);
    }

        //  This is an alias function of vmsProtectedDelta
    public function vms_protected_delta($value)
    {
        return $this->vmsProtectedDelta($value);
    }
    
    public function rpoBreachedVms($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('rpo_breached_vms', $operator, $value);
    }

        //  This is an alias function of rpoBreachedVms
    public function rpo_breached_vms($value)
    {
        return $this->rpoBreachedVms($value);
    }
    
    public function slaBreachedJobs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('sla_breached_jobs', $operator, $value);
    }

        //  This is an alias function of slaBreachedJobs
    public function sla_breached_jobs($value)
    {
        return $this->slaBreachedJobs($value);
    }
    
    public function jobsDisabled($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('jobs_disabled', $operator, $value);
    }

        //  This is an alias function of jobsDisabled
    public function jobs_disabled($value)
    {
        return $this->jobsDisabled($value);
    }
    
    public function jobsFailed24h($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('jobs_failed_24h', $operator, $value);
    }

        //  This is an alias function of jobsFailed24h
    public function jobs_failed_24h($value)
    {
        return $this->jobsFailed24h($value);
    }
    
    public function jobsFailed30d($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('jobs_failed_30d', $operator, $value);
    }

        //  This is an alias function of jobsFailed30d
    public function jobs_failed_30d($value)
    {
        return $this->jobsFailed30d($value);
    }
    
    public function storageUsedBytes($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('storage_used_bytes', $operator, $value);
    }

        //  This is an alias function of storageUsedBytes
    public function storage_used_bytes($value)
    {
        return $this->storageUsedBytes($value);
    }
    
    public function protectionsDone24h($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('protections_done_24h', $operator, $value);
    }

        //  This is an alias function of protectionsDone24h
    public function protections_done_24h($value)
    {
        return $this->protectionsDone24h($value);
    }
    
    public function protectionsDone30d($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('protections_done_30d', $operator, $value);
    }

        //  This is an alias function of protectionsDone30d
    public function protections_done_30d($value)
    {
        return $this->protectionsDone30d($value);
    }
    
    public function protectionsDoneDelta($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('protections_done_delta', $operator, $value);
    }

        //  This is an alias function of protectionsDoneDelta
    public function protections_done_delta($value)
    {
        return $this->protectionsDoneDelta($value);
    }
    
    public function jobsWithReplication($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('jobs_with_replication', $operator, $value);
    }

        //  This is an alias function of jobsWithReplication
    public function jobs_with_replication($value)
    {
        return $this->jobsWithReplication($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
