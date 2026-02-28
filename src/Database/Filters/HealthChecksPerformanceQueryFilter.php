<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class HealthChecksPerformanceQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function objectType($value)
    {
        return $this->builder->where('object_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of objectType
    public function object_type($value)
    {
        return $this->objectType($value);
    }
        
    public function overallStatus($value)
    {
        return $this->builder->where('overall_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of overallStatus
    public function overall_status($value)
    {
        return $this->overallStatus($value);
    }
    
    public function totalChecks($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_checks', $operator, $value);
    }

        //  This is an alias function of totalChecks
    public function total_checks($value)
    {
        return $this->totalChecks($value);
    }
    
    public function healthyCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('healthy_count', $operator, $value);
    }

        //  This is an alias function of healthyCount
    public function healthy_count($value)
    {
        return $this->healthyCount($value);
    }
    
    public function warningCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('warning_count', $operator, $value);
    }

        //  This is an alias function of warningCount
    public function warning_count($value)
    {
        return $this->warningCount($value);
    }
    
    public function criticalCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('critical_count', $operator, $value);
    }

        //  This is an alias function of criticalCount
    public function critical_count($value)
    {
        return $this->criticalCount($value);
    }
    
    public function failedCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('failed_count', $operator, $value);
    }

        //  This is an alias function of failedCount
    public function failed_count($value)
    {
        return $this->failedCount($value);
    }
    
    public function lastCheckAtStart($date)
    {
        return $this->builder->where('last_check_at', '>=', $date);
    }

    public function lastCheckAtEnd($date)
    {
        return $this->builder->where('last_check_at', '<=', $date);
    }

    //  This is an alias function of lastCheckAt
    public function last_check_at_start($value)
    {
        return $this->lastCheckAtStart($value);
    }

    //  This is an alias function of lastCheckAt
    public function last_check_at_end($value)
    {
        return $this->lastCheckAtEnd($value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE




}
