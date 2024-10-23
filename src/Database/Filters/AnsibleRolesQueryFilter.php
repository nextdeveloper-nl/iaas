<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class AnsibleRolesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }

        
    public function hash($value)
    {
        return $this->builder->where('hash', 'like', '%' . $value . '%');
    }

        
    public function minAnsibleVersion($value)
    {
        return $this->builder->where('min_ansible_version', 'like', '%' . $value . '%');
    }

        //  This is an alias function of minAnsibleVersion
    public function min_ansible_version($value)
    {
        return $this->minAnsibleVersion($value);
    }
        
    public function prerequisites($value)
    {
        return $this->builder->where('prerequisites', 'like', '%' . $value . '%');
    }

    
    public function version($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('version', $operator, $value);
    }

    
    public function releaseNumber($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('release_number', $operator, $value);
    }

        //  This is an alias function of releaseNumber
    public function release_number($value)
    {
        return $this->releaseNumber($value);
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
