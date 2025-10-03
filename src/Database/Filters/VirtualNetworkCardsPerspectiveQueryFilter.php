<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualNetworkCardsPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function network($value)
    {
        return $this->builder->where('network', 'ilike', '%' . $value . '%');
    }

        
    public function virtualMachine($value)
    {
        return $this->builder->where('virtual_machine', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of virtualMachine
    public function virtual_machine($value)
    {
        return $this->virtualMachine($value);
    }
        
    public function status($value)
    {
        return $this->builder->where('status', 'ilike', '%' . $value . '%');
    }

    
    public function bandwidthLimit($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('bandwidth_limit', $operator, $value);
    }

        //  This is an alias function of bandwidthLimit
    public function bandwidth_limit($value)
    {
        return $this->bandwidthLimit($value);
    }
    
    public function deviceNumber($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('device_number', $operator, $value);
    }

        //  This is an alias function of deviceNumber
    public function device_number($value)
    {
        return $this->deviceNumber($value);
    }
    
    public function isDraft($value)
    {
        return $this->builder->where('is_draft', $value);
    }

        //  This is an alias function of isDraft
    public function is_draft($value)
    {
        return $this->isDraft($value);
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
    
    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    
    public function iamUserId($value)
    {
            $iamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($iamUser) {
            return $this->builder->where('iam_user_id', '=', $iamUser->id);
        }
    }

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











}
