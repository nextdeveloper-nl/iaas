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
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }
    
    public function path($value)
    {
        return $this->builder->where('path', 'like', '%' . $value . '%');
    }
    
    public function filename($value)
    {
        return $this->builder->where('filename', 'like', '%' . $value . '%');
    }
    
    public function defaultUsername($value)
    {
        return $this->builder->where('default_username', 'like', '%' . $value . '%');
    }
    
    public function defaultPassword($value)
    {
        return $this->builder->where('default_password', 'like', '%' . $value . '%');
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
    
    public function releaseVersion($value)
    {
        return $this->builder->where('release_version', 'like', '%' . $value . '%');
    }
    
    public function extra($value)
    {
        return $this->builder->where('extra', 'like', '%' . $value . '%');
    }
    
    public function cpuType($value)
    {
        return $this->builder->where('cpu_type', 'like', '%' . $value . '%');
    }
    
    public function hash($value)
    {
        return $this->builder->where('hash', 'like', '%' . $value . '%');
    }

    public function isActive()
    {
        return $this->builder->where('is_active', true);
    }

    public function isIso()
    {
        return $this->builder->where('is_iso', true);
    }

    public function isVirtualMachineImage()
    {
        return $this->builder->where('is_virtual_machine_image', true);
    }

    public function isDockerImage()
    {
        return $this->builder->where('is_docker_image', true);
    }

    public function isLatest()
    {
        return $this->builder->where('is_latest', true);
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
