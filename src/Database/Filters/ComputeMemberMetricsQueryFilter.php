<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class ComputeMemberMetricsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function source($value)
    {
        return $this->builder->where('source', 'like', '%' . $value . '%');
    }

        
    public function parameter($value)
    {
        return $this->builder->where('parameter', 'like', '%' . $value . '%');
    }

    
    public function timestampStart($date)
    {
        return $this->builder->where('timestamp', '>=', $date);
    }

    public function timestampEnd($date)
    {
        return $this->builder->where('timestamp', '<=', $date);
    }

    //  This is an alias function of timestamp
    public function timestamp_start($value)
    {
        return $this->timestampStart($value);
    }

    //  This is an alias function of timestamp
    public function timestamp_end($value)
    {
        return $this->timestampEnd($value);
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

    public function iaasComputeMemberId($value)
    {
            $iaasComputeMember = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('uuid', $value)->first();

        if($iaasComputeMember) {
            return $this->builder->where('iaas_compute_member_id', '=', $iaasComputeMember->id);
        }
    }

        //  This is an alias function of iaasComputeMember
    public function iaas_compute_member_id($value)
    {
        return $this->iaasComputeMember($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE





















}
