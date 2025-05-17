<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachineBackupsQueryFilter extends AbstractQueryFilter
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


    public function username($value)
    {
        return $this->builder->where('username', 'ilike', '%' . $value . '%');
    }


    public function password($value)
    {
        return $this->builder->where('password', 'ilike', '%' . $value . '%');
    }


    public function hash($value)
    {
        return $this->builder->where('hash', 'ilike', '%' . $value . '%');
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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











}
