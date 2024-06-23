<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class RepositoriesPerspectiveQueryFilter extends AbstractQueryFilter
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
    
    public function repositoryMaintainer($value)
    {
        return $this->builder->where('repository_maintainer', 'like', '%' . $value . '%');
    }

    public function isoImageCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('iso_image_count', $operator, $value);
    }

    public function vmImageCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('vm_image_count', $operator, $value);
    }

    public function isActive($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_active', $value);
    }

    public function isVmRepo($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_vm_repo', $value);
    }

    public function isIsoRepo($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_iso_repo', $value);
    }

    public function isDockerRegistry($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_docker_registry', $value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
