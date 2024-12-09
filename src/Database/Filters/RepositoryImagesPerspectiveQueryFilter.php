<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class RepositoryImagesPerspectiveQueryFilter extends AbstractQueryFilter
{

    /**
     * @var Builder
     */
    protected $builder;

    public function image($value)
    {
        return $this->builder->where('image', 'like', '%' . $value . '%');
    }


    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }


    public function os($value)
    {
        return $this->builder->where('os', 'like', '%' . $value . '%');
    }


    public function distro($value)
    {
        return $this->builder->where('distro', 'ilike', '%' . $value . '%');
    }

    public function version($value)
    {
        return $this->builder->where('version', 'like', '%' . $value . '%');
    }


    public function repositoryName($value)
    {
        return $this->builder->where('repository_name', 'like', '%' . $value . '%');
    }

        //  This is an alias function of repositoryName
    public function repository_name($value)
    {
        return $this->repositoryName($value);
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

    public function isIso($value)
    {
        return $this->builder->where('is_iso', $value);
    }

        //  This is an alias function of isIso
    public function is_iso($value)
    {
        return $this->isIso($value);
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


    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE






















}
