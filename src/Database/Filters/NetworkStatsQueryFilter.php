<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class NetworkStatsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function totalTx($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_tx', $operator, $value);
    }

        //  This is an alias function of totalTx
    public function total_tx($value)
    {
        return $this->totalTx($value);
    }
    
    public function totalRx($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_rx', $operator, $value);
    }

        //  This is an alias function of totalRx
    public function total_rx($value)
    {
        return $this->totalRx($value);
    }
    
    public function totalIpAddress($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_ip_address', $operator, $value);
    }

        //  This is an alias function of totalIpAddress
    public function total_ip_address($value)
    {
        return $this->totalIpAddress($value);
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

    public function iaasNetworkId($value)
    {
            $iaasNetwork = \NextDeveloper\IAAS\Database\Models\Networks::where('uuid', $value)->first();

        if($iaasNetwork) {
            return $this->builder->where('iaas_network_id', '=', $iaasNetwork->id);
        }
    }

        //  This is an alias function of iaasNetwork
    public function iaas_network_id($value)
    {
        return $this->iaasNetwork($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE












































}
