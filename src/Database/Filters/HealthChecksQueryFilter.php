<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class HealthChecksQueryFilter extends AbstractQueryFilter
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
        
    public function checkType($value)
    {
        return $this->builder->where('check_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of checkType
    public function check_type($value)
    {
        return $this->checkType($value);
    }
        
    public function checkStatus($value)
    {
        return $this->builder->where('check_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of checkStatus
    public function check_status($value)
    {
        return $this->checkStatus($value);
    }
        
    public function severity($value)
    {
        return $this->builder->where('severity', 'ilike', '%' . $value . '%');
    }

        
    public function errorMessage($value)
    {
        return $this->builder->where('error_message', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of errorMessage
    public function error_message($value)
    {
        return $this->errorMessage($value);
    }
        
    public function errorCode($value)
    {
        return $this->builder->where('error_code', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of errorCode
    public function error_code($value)
    {
        return $this->errorCode($value);
    }
    
    public function responseTimeMs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('response_time_ms', $operator, $value);
    }

        //  This is an alias function of responseTimeMs
    public function response_time_ms($value)
    {
        return $this->responseTimeMs($value);
    }
    
    public function checkedAtStart($date)
    {
        return $this->builder->where('checked_at', '>=', $date);
    }

    public function checkedAtEnd($date)
    {
        return $this->builder->where('checked_at', '<=', $date);
    }

    //  This is an alias function of checkedAt
    public function checked_at_start($value)
    {
        return $this->checkedAtStart($value);
    }

    //  This is an alias function of checkedAt
    public function checked_at_end($value)
    {
        return $this->checkedAtEnd($value);
    }

    public function nextCheckAtStart($date)
    {
        return $this->builder->where('next_check_at', '>=', $date);
    }

    public function nextCheckAtEnd($date)
    {
        return $this->builder->where('next_check_at', '<=', $date);
    }

    //  This is an alias function of nextCheckAt
    public function next_check_at_start($value)
    {
        return $this->nextCheckAtStart($value);
    }

    //  This is an alias function of nextCheckAt
    public function next_check_at_end($value)
    {
        return $this->nextCheckAtEnd($value);
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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE



}
