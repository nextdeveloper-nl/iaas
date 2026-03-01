<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class BackupJobsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }

        
    public function type($value)
    {
        return $this->builder->where('type', 'ilike', '%' . $value . '%');
    }

        
    public function objectType($value)
    {
        return $this->builder->where('object_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of objectType
    public function object_type($value)
    {
        return $this->objectType($value);
    }
        
    public function notificationWebhook($value)
    {
        return $this->builder->where('notification_webhook', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of notificationWebhook
    public function notification_webhook($value)
    {
        return $this->notificationWebhook($value);
    }
    
    public function expectedRpoHours($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('expected_rpo_hours', $operator, $value);
    }

        //  This is an alias function of expectedRpoHours
    public function expected_rpo_hours($value)
    {
        return $this->expectedRpoHours($value);
    }
    
    public function expectedRtoHours($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('expected_rto_hours', $operator, $value);
    }

        //  This is an alias function of expectedRtoHours
    public function expected_rto_hours($value)
    {
        return $this->expectedRtoHours($value);
    }
    
    public function slaTargetPct($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('sla_target_pct', $operator, $value);
    }

        //  This is an alias function of slaTargetPct
    public function sla_target_pct($value)
    {
        return $this->slaTargetPct($value);
    }
    
    public function maxAllowedFailures($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('max_allowed_failures', $operator, $value);
    }

        //  This is an alias function of maxAllowedFailures
    public function max_allowed_failures($value)
    {
        return $this->maxAllowedFailures($value);
    }
    
    public function isEnabled($value)
    {
        return $this->builder->where('is_enabled', $value);
    }

        //  This is an alias function of isEnabled
    public function is_enabled($value)
    {
        return $this->isEnabled($value);
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

    public function iaasRepositoryId($value)
    {
            $iaasRepository = \NextDeveloper\IAAS\Database\Models\Repositories::where('uuid', $value)->first();

        if($iaasRepository) {
            return $this->builder->where('iaas_repository_id', '=', $iaasRepository->id);
        }
    }

        //  This is an alias function of iaasRepository
    public function iaas_repository_id($value)
    {
        return $this->iaasRepository($value);
    }
    
    public function iaasBackupRetentionPolicyId($value)
    {
            $iaasBackupRetentionPolicy = \NextDeveloper\IAAS\Database\Models\BackupRetentionPolicies::where('uuid', $value)->first();

        if($iaasBackupRetentionPolicy) {
            return $this->builder->where('iaas_backup_retention_policy_id', '=', $iaasBackupRetentionPolicy->id);
        }
    }

        //  This is an alias function of iaasBackupRetentionPolicy
    public function iaas_backup_retention_policy_id($value)
    {
        return $this->iaasBackupRetentionPolicy($value);
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
