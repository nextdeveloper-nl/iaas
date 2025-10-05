<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
            

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class BackupSchedulesQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;
    
    public function scheduleType($value)
    {
        return $this->builder->where('schedule_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of scheduleType
    public function schedule_type($value)
    {
        return $this->scheduleType($value);
    }
    
    public function dayOfMonth($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('day_of_month', $operator, $value);
    }

        //  This is an alias function of dayOfMonth
    public function day_of_month($value)
    {
        return $this->dayOfMonth($value);
    }
    
    public function dayOfWeek($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('day_of_week', $operator, $value);
    }

        //  This is an alias function of dayOfWeek
    public function day_of_week($value)
    {
        return $this->dayOfWeek($value);
    }
    
    public function timeOfDayStart($date)
    {
        return $this->builder->where('time_of_day', '>=', $date);
    }

    public function timeOfDayEnd($date)
    {
        return $this->builder->where('time_of_day', '<=', $date);
    }

    //  This is an alias function of timeOfDay
    public function time_of_day_start($value)
    {
        return $this->timeOfDayStart($value);
    }

    //  This is an alias function of timeOfDay
    public function time_of_day_end($value)
    {
        return $this->timeOfDayEnd($value);
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
