<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class RepositoryImagesQueryFilter extends AbstractQueryFilter
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


    public function defaultUsername($value)
    {
        return $this->builder->where('default_username', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of defaultUsername
    public function default_username($value)
    {
        return $this->defaultUsername($value);
    }

    public function defaultPassword($value)
    {
        return $this->builder->where('default_password', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of defaultPassword
    public function default_password($value)
    {
        return $this->defaultPassword($value);
    }

    public function os($value)
    {
        return $this->builder->where('os', 'ilike', '%' . $value . '%');
    }


    public function distro($value)
    {
        return $this->builder->where('distro', 'ilike', '%' . $value . '%');
    }


    public function version($value)
    {
        return $this->builder->where('version', 'ilike', '%' . $value . '%');
    }


    public function releaseVersion($value)
    {
        return $this->builder->where('release_version', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of releaseVersion
    public function release_version($value)
    {
        return $this->releaseVersion($value);
    }

    public function extra($value)
    {
        return $this->builder->where('extra', 'ilike', '%' . $value . '%');
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

    public function hash($value)
    {
        return $this->builder->where('hash', 'ilike', '%' . $value . '%');
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


    public function isActive($value)
    {
        return $this->builder->where('is_active', $value);
    }

        //  This is an alias function of isActive
    public function is_active($value)
    {
        return $this->isActive($value);
    }

    public function isIso($value)
    {
        return $this->builder->where('is_iso', $value);
    }

        //  This is an alias function of isIso
    public function is_iso($value)
    {
        return $this->isIso($value);
    }

    public function isVirtualMachineImage($value)
    {
        return $this->builder->where('is_virtual_machine_image', $value);
    }

        //  This is an alias function of isVirtualMachineImage
    public function is_virtual_machine_image($value)
    {
        return $this->isVirtualMachineImage($value);
    }

    public function isDockerImage($value)
    {
        return $this->builder->where('is_docker_image', $value);
    }

        //  This is an alias function of isDockerImage
    public function is_docker_image($value)
    {
        return $this->isDockerImage($value);
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

    public function isPublic($value)
    {
        return $this->builder->where('is_public', $value);
    }

        //  This is an alias function of isPublic
    public function is_public($value)
    {
        return $this->isPublic($value);
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
