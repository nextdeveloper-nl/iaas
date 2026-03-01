<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                    

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class BackupJobReplicationsQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function replicationType($value)
    {
        return $this->builder->where('replication_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of replicationType
    public function replication_type($value)
    {
        return $this->replicationType($value);
    }
        
    public function lastReplicationStatus($value)
    {
        return $this->builder->where('last_replication_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of lastReplicationStatus
    public function last_replication_status($value)
    {
        return $this->lastReplicationStatus($value);
    }
    
    public function priority($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('priority', $operator, $value);
    }

    
    public function bandwidthLimitMbps($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('bandwidth_limit_mbps', $operator, $value);
    }

        //  This is an alias function of bandwidthLimitMbps
    public function bandwidth_limit_mbps($value)
    {
        return $this->bandwidthLimitMbps($value);
    }
    
    public function lastReplicationSizeBytes($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('last_replication_size_bytes', $operator, $value);
    }

        //  This is an alias function of lastReplicationSizeBytes
    public function last_replication_size_bytes($value)
    {
        return $this->lastReplicationSizeBytes($value);
    }
    
    public function lastReplicationDurationSecs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('last_replication_duration_secs', $operator, $value);
    }

        //  This is an alias function of lastReplicationDurationSecs
    public function last_replication_duration_secs($value)
    {
        return $this->lastReplicationDurationSecs($value);
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
     
    public function lastReplicatedAtStart($date)
    {
        return $this->builder->where('last_replicated_at', '>=', $date);
    }

    public function lastReplicatedAtEnd($date)
    {
        return $this->builder->where('last_replicated_at', '<=', $date);
    }

    //  This is an alias function of lastReplicatedAt
    public function last_replicated_at_start($value)
    {
        return $this->lastReplicatedAtStart($value);
    }

    //  This is an alias function of lastReplicatedAt
    public function last_replicated_at_end($value)
    {
        return $this->lastReplicatedAtEnd($value);
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

    public function iaasBackupJobId($value)
    {
            $iaasBackupJob = \NextDeveloper\IAAS\Database\Models\BackupJobs::where('uuid', $value)->first();

        if($iaasBackupJob) {
            return $this->builder->where('iaas_backup_job_id', '=', $iaasBackupJob->id);
        }
    }

        //  This is an alias function of iaasBackupJob
    public function iaas_backup_job_id($value)
    {
        return $this->iaasBackupJob($value);
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
