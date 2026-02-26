<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class LargestTenantsPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function vmCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vm_count', $operator, $value);
    }

        //  This is an alias function of vmCount
    public function vm_count($value)
    {
        return $this->vmCount($value);
    }
    
    public function vcpuTotal($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vcpu_total', $operator, $value);
    }

        //  This is an alias function of vcpuTotal
    public function vcpu_total($value)
    {
        return $this->vcpuTotal($value);
    }
    
    public function ramTotalGb($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ram_total_gb', $operator, $value);
    }

        //  This is an alias function of ramTotalGb
    public function ram_total_gb($value)
    {
        return $this->ramTotalGb($value);
    }
    
    public function diskCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('disk_count', $operator, $value);
    }

        //  This is an alias function of diskCount
    public function disk_count($value)
    {
        return $this->diskCount($value);
    }
    
    public function networkCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('network_count', $operator, $value);
    }

        //  This is an alias function of networkCount
    public function network_count($value)
    {
        return $this->networkCount($value);
    }
    
    public function iaasAccountId($value)
    {
            $iaasAccount = \NextDeveloper\IAAS\Database\Models\Accounts::where('uuid', $value)->first();

        if($iaasAccount) {
            return $this->builder->where('iaas_account_id', '=', $iaasAccount->id);
        }
    }

        //  This is an alias function of iaasAccount
    public function iaas_account_id($value)
    {
        return $this->iaasAccount($value);
    }
    
    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE



}
