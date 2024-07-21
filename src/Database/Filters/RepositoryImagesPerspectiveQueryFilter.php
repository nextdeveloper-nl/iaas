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
    
    public function os($value)
    {
        return $this->builder->where('os', 'like', '%' . $value . '%');
    }
    
    public function distro($value)
    {
        return $this->builder->where('distro', 'like', '%' . $value . '%');
    }
    
    public function version($value)
    {
        return $this->builder->where('version', 'like', '%' . $value . '%');
    }
    
    public function repositoryName($value)
    {
        return $this->builder->where('repository_name', 'like', '%' . $value . '%');
    }

    public function isLatest($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_latest', $value);
    }

    public function isIso($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_iso', $value);
    }

    public function isPublic($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_public', $value);
    }

    public function isVirtualMachineImage($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_virtual_machine_image', $value);
    }

    public function isDockerImage($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_docker_image', $value);
    }

    public function createdAtStart($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function createdAtEnd($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }

    public function updatedAtStart($date)
    {
        return $this->builder->where('updated_at', '>=', $date);
    }

    public function updatedAtEnd($date)
    {
        return $this->builder->where('updated_at', '<=', $date);
    }

    public function deletedAtStart($date)
    {
        return $this->builder->where('deleted_at', '>=', $date);
    }

    public function deletedAtEnd($date)
    {
        return $this->builder->where('deleted_at', '<=', $date);
    }

    public function iaasRepositoryId($value)
    {
            $iaasRepository = \NextDeveloper\IAAS\Database\Models\Repositories::where('uuid', $value)->first();

        if($iaasRepository) {
            return $this->builder->where('iaas_repository_id', '=', $iaasRepository->id);
        }
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
