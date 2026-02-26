<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class AnsibleSystemPlaybooksQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function slug($value)
    {
        return $this->builder->where('slug', 'ilike', '%' . $value . '%');
    }

        
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function description($value)
    {
        return $this->builder->where('description', 'ilike', '%' . $value . '%');
    }

        
    public function package($value)
    {
        return $this->builder->where('package', 'ilike', '%' . $value . '%');
    }

        
    public function path($value)
    {
        return $this->builder->where('path', 'ilike', '%' . $value . '%');
    }

        
    public function playbookFilename($value)
    {
        return $this->builder->where('playbook_filename', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of playbookFilename
    public function playbook_filename($value)
    {
        return $this->playbookFilename($value);
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
     
    public function isProcedure($value)
    {
        return $this->builder->where('is_procedure', $value);
    }

        //  This is an alias function of isProcedure
    public function is_procedure($value)
    {
        return $this->isProcedure($value);
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

    
    public function iaasAnsibleServerId($value)
    {
            $iaasAnsibleServer = \NextDeveloper\IAAS\Database\Models\AnsibleServers::where('uuid', $value)->first();

        if($iaasAnsibleServer) {
            return $this->builder->where('iaas_ansible_server_id', '=', $iaasAnsibleServer->id);
        }
    }

        //  This is an alias function of iaasAnsibleServer
    public function iaas_ansible_server_id($value)
    {
        return $this->iaasAnsibleServer($value);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
















































}
