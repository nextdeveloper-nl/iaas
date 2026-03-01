<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VmBackupHeatmapByCloudsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function cloudNodeName($value)
    {
        return $this->builder->where('cloud_node_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of cloudNodeName
    public function cloud_node_name($value)
    {
        return $this->cloudNodeName($value);
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
        
    public function dayOfWeek($value)
    {
        return $this->builder->where('day_of_week', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of dayOfWeek
    public function day_of_week($value)
    {
        return $this->dayOfWeek($value);
    }
        
    public function dayStatus($value)
    {
        return $this->builder->where('day_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of dayStatus
    public function day_status($value)
    {
        return $this->dayStatus($value);
    }
    
    public function dayOffset($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('day_offset', $operator, $value);
    }

        //  This is an alias function of dayOffset
    public function day_offset($value)
    {
        return $this->dayOffset($value);
    }
    
    public function distinctJobs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('distinct_jobs', $operator, $value);
    }

        //  This is an alias function of distinctJobs
    public function distinct_jobs($value)
    {
        return $this->distinctJobs($value);
    }
    
    public function rpoBreachCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('rpo_breach_count', $operator, $value);
    }

        //  This is an alias function of rpoBreachCount
    public function rpo_breach_count($value)
    {
        return $this->rpoBreachCount($value);
    }
    
    public function totalRuns($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_runs', $operator, $value);
    }

        //  This is an alias function of totalRuns
    public function total_runs($value)
    {
        return $this->totalRuns($value);
    }
    
    public function successRuns($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('success_runs', $operator, $value);
    }

        //  This is an alias function of successRuns
    public function success_runs($value)
    {
        return $this->successRuns($value);
    }
    
    public function failedRuns($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('failed_runs', $operator, $value);
    }

        //  This is an alias function of failedRuns
    public function failed_runs($value)
    {
        return $this->failedRuns($value);
    }
    
    public function daySizeBytes($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('day_size_bytes', $operator, $value);
    }

        //  This is an alias function of daySizeBytes
    public function day_size_bytes($value)
    {
        return $this->daySizeBytes($value);
    }
    
    public function avgDurationSecs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('avg_duration_secs', $operator, $value);
    }

        //  This is an alias function of avgDurationSecs
    public function avg_duration_secs($value)
    {
        return $this->avgDurationSecs($value);
    }
    
    public function backupDateStart($date)
    {
        return $this->builder->where('backup_date', '>=', $date);
    }

    public function backupDateEnd($date)
    {
        return $this->builder->where('backup_date', '<=', $date);
    }

    //  This is an alias function of backupDate
    public function backup_date_start($value)
    {
        return $this->backupDateStart($value);
    }

    //  This is an alias function of backupDate
    public function backup_date_end($value)
    {
        return $this->backupDateEnd($value);
    }

    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

        //  This is an alias function of iaasCloudNode
    public function iaas_cloud_node_id($value)
    {
        return $this->iaasCloudNode($value);
    }
    
    public function iaasDatacenterId($value)
    {
            $iaasDatacenter = \NextDeveloper\IAAS\Database\Models\Datacenters::where('uuid', $value)->first();

        if($iaasDatacenter) {
            return $this->builder->where('iaas_datacenter_id', '=', $iaasDatacenter->id);
        }
    }

        //  This is an alias function of iaasDatacenter
    public function iaas_datacenter_id($value)
    {
        return $this->iaasDatacenter($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
