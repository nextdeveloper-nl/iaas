<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class RepositoriesPerspectiveQueryFilter extends AbstractQueryFilter
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

        
    public function repositoryMaintainer($value)
    {
        return $this->builder->where('repository_maintainer', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of repositoryMaintainer
    public function repository_maintainer($value)
    {
        return $this->repositoryMaintainer($value);
    }
    
    public function isoImageCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('iso_image_count', $operator, $value);
    }

        //  This is an alias function of isoImageCount
    public function iso_image_count($value)
    {
        return $this->isoImageCount($value);
    }
    
    public function vmImageCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vm_image_count', $operator, $value);
    }

        //  This is an alias function of vmImageCount
    public function vm_image_count($value)
    {
        return $this->vmImageCount($value);
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
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











































}
