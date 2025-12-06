<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                        

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachineBackupsPerspectiveQueryFilter extends AbstractQueryFilter
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

        
    public function path($value)
    {
        return $this->builder->where('path', 'ilike', '%' . $value . '%');
    }

        
    public function filename($value)
    {
        return $this->builder->where('filename', 'ilike', '%' . $value . '%');
    }

        
    public function backupType($value)
    {
        return $this->builder->where('backup_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of backupType
    public function backup_type($value)
    {
        return $this->backupType($value);
    }
        
    public function status($value)
    {
        return $this->builder->where('status', 'ilike', '%' . $value . '%');
    }

        
    public function hash($value)
    {
        return $this->builder->where('hash', 'ilike', '%' . $value . '%');
    }

        
    public function os($value)
    {
        return $this->builder->where('os', 'ilike', '%' . $value . '%');
    }

        
    public function distro($value)
    {
        return $this->builder->where('distro', 'ilike', '%' . $value . '%');
    }

        
    public function cpuType($value)
    {
        return $this->builder->where('cpu_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of cpuType
    public function cpu_type($value)
    {
        return $this->cpuType($value);
    }
        
    public function hostname($value)
    {
        return $this->builder->where('hostname', 'ilike', '%' . $value . '%');
    }

    
    public function cpu($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('cpu', $operator, $value);
    }

    
    public function ram($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('ram', $operator, $value);
    }

    
    public function progress($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('progress', $operator, $value);
    }

    
    public function size($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('size', $operator, $value);
    }

    
    public function isLatest($value)
    {
        return $this->builder->where('is_latest', $value);
    }

        //  This is an alias function of isLatest
    public function is_latest($value)
    {
        return $this->isLatest($value);
    }
     
    public function backupStartsStart($date)
    {
        return $this->builder->where('backup_starts', '>=', $date);
    }

    public function backupStartsEnd($date)
    {
        return $this->builder->where('backup_starts', '<=', $date);
    }

    //  This is an alias function of backupStarts
    public function backup_starts_start($value)
    {
        return $this->backupStartsStart($value);
    }

    //  This is an alias function of backupStarts
    public function backup_starts_end($value)
    {
        return $this->backupStartsEnd($value);
    }

    public function backupEndsStart($date)
    {
        return $this->builder->where('backup_ends', '>=', $date);
    }

    public function backupEndsEnd($date)
    {
        return $this->builder->where('backup_ends', '<=', $date);
    }

    //  This is an alias function of backupEnds
    public function backup_ends_start($value)
    {
        return $this->backupEndsStart($value);
    }

    //  This is an alias function of backupEnds
    public function backup_ends_end($value)
    {
        return $this->backupEndsEnd($value);
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

    
    public function iaasRepositoryImageId($value)
    {
            $iaasRepositoryImage = \NextDeveloper\IAAS\Database\Models\RepositoryImages::where('uuid', $value)->first();

        if($iaasRepositoryImage) {
            return $this->builder->where('iaas_repository_image_id', '=', $iaasRepositoryImage->id);
        }
    }

        //  This is an alias function of iaasRepositoryImage
    public function iaas_repository_image_id($value)
    {
        return $this->iaasRepositoryImage($value);
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
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
