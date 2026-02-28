<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class CloudNodeDailyStatsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function maxVmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('max_vm_count', $operator, $value);
    }

        //  This is an alias function of maxVmCount
    public function max_vm_count($value)
    {
        return $this->maxVmCount($value);
    }
    
    public function maxVcpus($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('max_vcpus', $operator, $value);
    }

        //  This is an alias function of maxVcpus
    public function max_vcpus($value)
    {
        return $this->maxVcpus($value);
    }
    
    public function maxRamGb($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('max_ram_gb', $operator, $value);
    }

        //  This is an alias function of maxRamGb
    public function max_ram_gb($value)
    {
        return $this->maxRamGb($value);
    }
    
    public function statDateStart($date)
    {
        return $this->builder->where('stat_date', '>=', $date);
    }

    public function statDateEnd($date)
    {
        return $this->builder->where('stat_date', '<=', $date);
    }

    //  This is an alias function of statDate
    public function stat_date_start($value)
    {
        return $this->statDateStart($value);
    }

    //  This is an alias function of statDate
    public function stat_date_end($value)
    {
        return $this->statDateEnd($value);
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
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}
