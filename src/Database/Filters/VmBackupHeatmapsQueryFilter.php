<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VmBackupHeatmapsQueryFilter extends AbstractQueryFilter
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

        
    public function dayOfWeek($value)
    {
        return $this->builder->where('day_of_week', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of dayOfWeek
    public function day_of_week($value)
    {
        return $this->dayOfWeek($value);
    }
        
    public function dayStatus($value)
    {
        return $this->builder->where('day_status', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of dayStatus
    public function day_status($value)
    {
        return $this->dayStatus($value);
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
    
    public function dayOffset($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('day_offset', $operator, $value);
    }

        //  This is an alias function of dayOffset
    public function day_offset($value)
    {
        return $this->dayOffset($value);
    }
    
    public function totalRuns($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('total_runs', $operator, $value);
    }

        //  This is an alias function of totalRuns
    public function total_runs($value)
    {
        return $this->totalRuns($value);
    }
    
    public function successRuns($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('success_runs', $operator, $value);
    }

        //  This is an alias function of successRuns
    public function success_runs($value)
    {
        return $this->successRuns($value);
    }
    
    public function failedRuns($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('failed_runs', $operator, $value);
    }

        //  This is an alias function of failedRuns
    public function failed_runs($value)
    {
        return $this->failedRuns($value);
    }
    
    public function daySizeBytes($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('day_size_bytes', $operator, $value);
    }

        //  This is an alias function of daySizeBytes
    public function day_size_bytes($value)
    {
        return $this->daySizeBytes($value);
    }
    
    public function avgDurationSecs($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('avg_duration_secs', $operator, $value);
    }

        //  This is an alias function of avgDurationSecs
    public function avg_duration_secs($value)
    {
        return $this->avgDurationSecs($value);
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
     
    public function isRpoBreach($value)
    {
        return $this->builder->where('is_rpo_breach', $value);
    }

        //  This is an alias function of isRpoBreach
    public function is_rpo_breach($value)
    {
        return $this->isRpoBreach($value);
    }
     
    public function backupDateStart($date)
    {
        return $this->builder->where('backup_date', '>=', $date);
    }

    public function backupDateEnd($date)
    {
        return $this->builder->where('backup_date', '<=', $date);
    }

    //  This is an alias function of backupDate
    public function backup_date_start($value)
    {
        return $this->backupDateStart($value);
    }

    //  This is an alias function of backupDate
    public function backup_date_end($value)
    {
        return $this->backupDateEnd($value);
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
    
    public function iamAccountId($value)
    {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::where('uuid', $value)->first();

        if($iamAccount) {
            return $this->builder->where('iam_account_id', '=', $iamAccount->id);
        }
    }

    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
