<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class AnsibleServersQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function sshUsername($value)
    {
        return $this->builder->where('ssh_username', 'like', '%' . $value . '%');
    }
    
    public function sshPassword($value)
    {
        return $this->builder->where('ssh_password', 'like', '%' . $value . '%');
    }
    
    public function rolesPath($value)
    {
        return $this->builder->where('roles_path', 'like', '%' . $value . '%');
    }
    
    public function systemPlaybooksPath($value)
    {
        return $this->builder->where('system_playbooks_path', 'like', '%' . $value . '%');
    }
    
    public function executionPath($value)
    {
        return $this->builder->where('execution_path', 'like', '%' . $value . '%');
    }

    public function sshPort($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ssh_port', $operator, $value);
    }

    public function ansibleVersion($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ansible_version', $operator, $value);
    }

    public function isExternalMachine($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_external_machine', $value);
    }

    public function isActive($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_active', $value);
    }

    public function isPublic($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_public', $value);
    }

    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    public function iaasVirtualMachineId($value)
    {
            $iaasVirtualMachine = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('uuid', $value)->first();

        if($iaasVirtualMachine) {
            return $this->builder->where('iaas_virtual_machine_id', '=', $iaasVirtualMachine->id);
        }
    }

    public function commonCurrencyId($value)
    {
            $commonCurrency = \NextDeveloper\Commons\Database\Models\Currencies::where('uuid', $value)->first();

        if($commonCurrency) {
            return $this->builder->where('common_currency_id', '=', $commonCurrency->id);
        }
    }

    public function iamUserId($value)
    {
            $iamUser = \NextDeveloper\IAM\Database\Models\Users::where('uuid', $value)->first();

        if($iamUser) {
            return $this->builder->where('iam_user_id', '=', $iamUser->id);
        }
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
