<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class GatewaysQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }


    public function sshUsername($value)
    {
        return $this->builder->where('ssh_username', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of sshUsername
    public function ssh_username($value)
    {
        return $this->sshUsername($value);
    }

    public function sshPassword($value)
    {
        return $this->builder->where('ssh_password', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of sshPassword
    public function ssh_password($value)
    {
        return $this->sshPassword($value);
    }

    public function apiToken($value)
    {
        return $this->builder->where('api_token', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of apiToken
    public function api_token($value)
    {
        return $this->apiToken($value);
    }

    public function apiUrl($value)
    {
        return $this->builder->where('api_url', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of apiUrl
    public function api_url($value)
    {
        return $this->apiUrl($value);
    }

    public function gatewayType($value)
    {
        return $this->builder->where('gateway_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of gatewayType
    public function gateway_type($value)
    {
        return $this->gatewayType($value);
    }

    public function isPublic($value)
    {
        return $this->builder->where('is_public', $value);
    }

        //  This is an alias function of isPublic
    public function is_public($value)
    {
        return $this->isPublic($value);
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
