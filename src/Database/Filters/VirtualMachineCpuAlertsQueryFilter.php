<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachineCpuAlertsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function severity($value)
    {
        return $this->builder->where('severity', 'ilike', '%' . $value . '%');
    }

        
    public function alertReason($value)
    {
        return $this->builder->where('alert_reason', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of alertReason
    public function alert_reason($value)
    {
        return $this->alertReason($value);
    }
    
    public function checkDurationMs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('check_duration_ms', $operator, $value);
    }

        //  This is an alias function of checkDurationMs
    public function check_duration_ms($value)
    {
        return $this->checkDurationMs($value);
    }
    
    public function alertTimeStart($date)
    {
        return $this->builder->where('alert_time', '>=', $date);
    }

    public function alertTimeEnd($date)
    {
        return $this->builder->where('alert_time', '<=', $date);
    }

    //  This is an alias function of alertTime
    public function alert_time_start($value)
    {
        return $this->alertTimeStart($value);
    }

    //  This is an alias function of alertTime
    public function alert_time_end($value)
    {
        return $this->alertTimeEnd($value);
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

    public function iaasVirtualMachineId($value)
    {
            $iaasVirtualMachine = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('uuid', $value)->first();

        if($iaasVirtualMachine) {
            return $this->builder->where('iaas_virtual_machine_id', '=', $iaasVirtualMachine->id);
        }
    }

        //  This is an alias function of iaasVirtualMachine
    public function iaas_virtual_machine_id($value)
    {
        return $this->iaasVirtualMachine($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE









}
