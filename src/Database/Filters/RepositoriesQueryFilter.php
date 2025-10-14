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
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function description($value)
    {
        return $this->builder->where('description', 'ilike', '%' . $value . '%');
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
        
    public function lastHash($value)
    {
        return $this->builder->where('last_hash', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of lastHash
    public function last_hash($value)
    {
        return $this->lastHash($value);
    }
        
    public function isoPath($value)
    {
        return $this->builder->where('iso_path', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of isoPath
    public function iso_path($value)
    {
        return $this->isoPath($value);
    }
        
    public function vmPath($value)
    {
        return $this->builder->where('vm_path', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of vmPath
    public function vm_path($value)
    {
        return $this->vmPath($value);
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

        //  This is an alias function of dockerRegistryPort
    public function docker_registry_port($value)
    {
        return $this->dockerRegistryPort($value);
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
     
    public function isVmRepo($value)
    {
        return $this->builder->where('is_vm_repo', $value);
    }

        //  This is an alias function of isVmRepo
    public function is_vm_repo($value)
    {
        return $this->isVmRepo($value);
    }
     
    public function isIsoRepo($value)
    {
        return $this->builder->where('is_iso_repo', $value);
    }

        //  This is an alias function of isIsoRepo
    public function is_iso_repo($value)
    {
        return $this->isIsoRepo($value);
    }
     
    public function isDockerRegistry($value)
    {
        return $this->builder->where('is_docker_registry', $value);
    }

        //  This is an alias function of isDockerRegistry
    public function is_docker_registry($value)
    {
        return $this->isDockerRegistry($value);
    }
     
    public function isBehindFirewall($value)
    {
        return $this->builder->where('is_behind_firewall', $value);
    }

        //  This is an alias function of isBehindFirewall
    public function is_behind_firewall($value)
    {
        return $this->isBehindFirewall($value);
    }
     
    public function isManagementAgentAvailable($value)
    {
        return $this->builder->where('is_management_agent_available', $value);
    }

        //  This is an alias function of isManagementAgentAvailable
    public function is_management_agent_available($value)
    {
        return $this->isManagementAgentAvailable($value);
    }
     
    public function isBackupRepository($value)
    {
        return $this->builder->where('is_backup_repository', $value);
    }

        //  This is an alias function of isBackupRepository
    public function is_backup_repository($value)
    {
        return $this->isBackupRepository($value);
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
    
    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

        //  This is an alias function of iaasCloudNode
    public function iaas_cloud_node_id($value)
    {
        return $this->iaasCloudNode($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE









































}
