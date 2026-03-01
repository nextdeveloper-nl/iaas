<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VmBackupJobsPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function jobName($value)
    {
        return $this->builder->where('job_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of jobName
    public function job_name($value)
    {
        return $this->jobName($value);
    }
        
    public function jobType($value)
    {
        return $this->builder->where('job_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of jobType
    public function job_type($value)
    {
        return $this->jobType($value);
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
        
    public function virtualMachineName($value)
    {
        return $this->builder->where('virtual_machine_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of virtualMachineName
    public function virtual_machine_name($value)
    {
        return $this->virtualMachineName($value);
    }
        
    public function hostname($value)
    {
        return $this->builder->where('hostname', 'ilike', '%' . $value . '%');
    }

        
    public function retentionPolicyName($value)
    {
        return $this->builder->where('retention_policy_name', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of retentionPolicyName
    public function retention_policy_name($value)
    {
        return $this->retentionPolicyName($value);
    }
        
    public function lastRunStatus($value)
    {
        return $this->builder->where('last_run_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of lastRunStatus
    public function last_run_status($value)
    {
        return $this->lastRunStatus($value);
    }
        
    public function statusIndicator($value)
    {
        return $this->builder->where('status_indicator', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of statusIndicator
    public function status_indicator($value)
    {
        return $this->statusIndicator($value);
    }
        
    public function replicationStatusIndicator($value)
    {
        return $this->builder->where('replication_status_indicator', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of replicationStatusIndicator
    public function replication_status_indicator($value)
    {
        return $this->replicationStatusIndicator($value);
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
    
    public function keepForDays($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('keep_for_days', $operator, $value);
    }

        //  This is an alias function of keepForDays
    public function keep_for_days($value)
    {
        return $this->keepForDays($value);
    }
    
    public function keepLastNBackups($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('keep_last_n_backups', $operator, $value);
    }

        //  This is an alias function of keepLastNBackups
    public function keep_last_n_backups($value)
    {
        return $this->keepLastNBackups($value);
    }
    
    public function lastRunProgress($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('last_run_progress', $operator, $value);
    }

        //  This is an alias function of lastRunProgress
    public function last_run_progress($value)
    {
        return $this->lastRunProgress($value);
    }
    
    public function lastRunDurationSecs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('last_run_duration_secs', $operator, $value);
    }

        //  This is an alias function of lastRunDurationSecs
    public function last_run_duration_secs($value)
    {
        return $this->lastRunDurationSecs($value);
    }
    
    public function lastRunSizeBytes($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('last_run_size_bytes', $operator, $value);
    }

        //  This is an alias function of lastRunSizeBytes
    public function last_run_size_bytes($value)
    {
        return $this->lastRunSizeBytes($value);
    }
    
    public function consecutiveFailures($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('consecutive_failures', $operator, $value);
    }

        //  This is an alias function of consecutiveFailures
    public function consecutive_failures($value)
    {
        return $this->consecutiveFailures($value);
    }
    
    public function rpoAchievedHours($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('rpo_achieved_hours', $operator, $value);
    }

        //  This is an alias function of rpoAchievedHours
    public function rpo_achieved_hours($value)
    {
        return $this->rpoAchievedHours($value);
    }
    
    public function replicationCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('replication_count', $operator, $value);
    }

        //  This is an alias function of replicationCount
    public function replication_count($value)
    {
        return $this->replicationCount($value);
    }
    
    public function replicationOkCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('replication_ok_count', $operator, $value);
    }

        //  This is an alias function of replicationOkCount
    public function replication_ok_count($value)
    {
        return $this->replicationOkCount($value);
    }
    
    public function replicationFailedCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('replication_failed_count', $operator, $value);
    }

        //  This is an alias function of replicationFailedCount
    public function replication_failed_count($value)
    {
        return $this->replicationFailedCount($value);
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
     
    public function isScheduled($value)
    {
        return $this->builder->where('is_scheduled', $value);
    }

        //  This is an alias function of isScheduled
    public function is_scheduled($value)
    {
        return $this->isScheduled($value);
    }
     
    public function lastRunAtStart($date)
    {
        return $this->builder->where('last_run_at', '>=', $date);
    }

    public function lastRunAtEnd($date)
    {
        return $this->builder->where('last_run_at', '<=', $date);
    }

    //  This is an alias function of lastRunAt
    public function last_run_at_start($value)
    {
        return $this->lastRunAtStart($value);
    }

    //  This is an alias function of lastRunAt
    public function last_run_at_end($value)
    {
        return $this->lastRunAtEnd($value);
    }

    public function lastRunEndedAtStart($date)
    {
        return $this->builder->where('last_run_ended_at', '>=', $date);
    }

    public function lastRunEndedAtEnd($date)
    {
        return $this->builder->where('last_run_ended_at', '<=', $date);
    }

    //  This is an alias function of lastRunEndedAt
    public function last_run_ended_at_start($value)
    {
        return $this->lastRunEndedAtStart($value);
    }

    //  This is an alias function of lastRunEndedAt
    public function last_run_ended_at_end($value)
    {
        return $this->lastRunEndedAtEnd($value);
    }

    public function lastReplicationAtStart($date)
    {
        return $this->builder->where('last_replication_at', '>=', $date);
    }

    public function lastReplicationAtEnd($date)
    {
        return $this->builder->where('last_replication_at', '<=', $date);
    }

    //  This is an alias function of lastReplicationAt
    public function last_replication_at_start($value)
    {
        return $this->lastReplicationAtStart($value);
    }

    //  This is an alias function of lastReplicationAt
    public function last_replication_at_end($value)
    {
        return $this->lastReplicationAtEnd($value);
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

    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
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
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
