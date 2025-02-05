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

        //  This is an alias function of sshUsername
    public function ssh_username($value)
    {
        return $this->sshUsername($value);
    }
        
    public function sshPassword($value)
    {
        return $this->builder->where('ssh_password', 'like', '%' . $value . '%');
    }

        //  This is an alias function of sshPassword
    public function ssh_password($value)
    {
        return $this->sshPassword($value);
    }
        
    public function rolesPath($value)
    {
        return $this->builder->where('roles_path', 'like', '%' . $value . '%');
    }

        //  This is an alias function of rolesPath
    public function roles_path($value)
    {
        return $this->rolesPath($value);
    }
        
    public function systemPlaybooksPath($value)
    {
        return $this->builder->where('system_playbooks_path', 'like', '%' . $value . '%');
    }

        //  This is an alias function of systemPlaybooksPath
    public function system_playbooks_path($value)
    {
        return $this->systemPlaybooksPath($value);
    }
        
    public function executionPath($value)
    {
        return $this->builder->where('execution_path', 'like', '%' . $value . '%');
    }

        //  This is an alias function of executionPath
    public function execution_path($value)
    {
        return $this->executionPath($value);
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

        //  This is an alias function of sshPort
    public function ssh_port($value)
    {
        return $this->sshPort($value);
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

        //  This is an alias function of ansibleVersion
    public function ansible_version($value)
    {
        return $this->ansibleVersion($value);
    }
    
    public function isExternalMachine($value)
    {
        return $this->builder->where('is_external_machine', $value);
    }

        //  This is an alias function of isExternalMachine
    public function is_external_machine($value)
    {
        return $this->isExternalMachine($value);
    }
     
    public function isActive($value)
    {
        return $this->builder->where('is_active', $value);
    }

        //  This is an alias function of isActive
    public function is_active($value)
    {
        return $this->isActive($value);
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
    
    public function commonCurrencyId($value)
    {
            $commonCurrency = \NextDeveloper\Commons\Database\Models\Currencies::where('uuid', $value)->first();

        if($commonCurrency) {
            return $this->builder->where('common_currency_id', '=', $commonCurrency->id);
        }
    }

        //  This is an alias function of commonCurrency
    public function common_currency_id($value)
    {
        return $this->commonCurrency($value);
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
