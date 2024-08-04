<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class RepositoriesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }
    
    public function sshUsername($value)
    {
        return $this->builder->where('ssh_username', 'like', '%' . $value . '%');
    }
    
    public function sshPassword($value)
    {
        return $this->builder->where('ssh_password', 'like', '%' . $value . '%');
    }
    
    public function lastHash($value)
    {
        return $this->builder->where('last_hash', 'like', '%' . $value . '%');
    }
    
    public function isoPath($value)
    {
        return $this->builder->where('iso_path', 'like', '%' . $value . '%');
    }
    
    public function vmPath($value)
    {
        return $this->builder->where('vm_path', 'like', '%' . $value . '%');
    }

    public function dockerRegistryPort($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('docker_registry_port', $operator, $value);
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

    public function isVmRepo($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_vm_repo', $value);
    }

    public function isIsoRepo($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_iso_repo', $value);
    }

    public function isDockerRegistry($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_docker_registry', $value);
    }

    public function isBehindFirewall($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_behind_firewall', $value);
    }

    public function isManagementAgentAvailable($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_management_agent_available', $value);
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
