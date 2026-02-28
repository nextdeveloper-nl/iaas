<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VmHourlyStatsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function cpu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('cpu', $operator, $value);
    }

    
    public function ram($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ram', $operator, $value);
    }

    
    public function statHourStart($date)
    {
        return $this->builder->where('stat_hour', '>=', $date);
    }

    public function statHourEnd($date)
    {
        return $this->builder->where('stat_hour', '<=', $date);
    }

    //  This is an alias function of statHour
    public function stat_hour_start($value)
    {
        return $this->statHourStart($value);
    }

    //  This is an alias function of statHour
    public function stat_hour_end($value)
    {
        return $this->statHourEnd($value);
    }

    public function validFromStart($date)
    {
        return $this->builder->where('valid_from', '>=', $date);
    }

    public function validFromEnd($date)
    {
        return $this->builder->where('valid_from', '<=', $date);
    }

    //  This is an alias function of validFrom
    public function valid_from_start($value)
    {
        return $this->validFromStart($value);
    }

    //  This is an alias function of validFrom
    public function valid_from_end($value)
    {
        return $this->validFromEnd($value);
    }

    public function validToStart($date)
    {
        return $this->builder->where('valid_to', '>=', $date);
    }

    public function validToEnd($date)
    {
        return $this->builder->where('valid_to', '<=', $date);
    }

    //  This is an alias function of validTo
    public function valid_to_start($value)
    {
        return $this->validToStart($value);
    }

    //  This is an alias function of validTo
    public function valid_to_end($value)
    {
        return $this->validToEnd($value);
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
