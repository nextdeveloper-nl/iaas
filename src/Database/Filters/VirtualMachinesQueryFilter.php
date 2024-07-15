<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
                                

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMachinesQueryFilter extends AbstractQueryFilter
{
    /**
     * Filter by tags
     *
     * @param  $values
     * @return Builder
     */
    public function tags($values)
    {
        $tags = explode(',', $values);

        $search = '';

        for($i = 0; $i < count($tags); $i++) {
            $search .= "'" . trim($tags[$i]) . "',";
        }

        $search = substr($search, 0, -1);

        return $this->builder->whereRaw('tags @> ARRAY[' . $search . ']');
    }

    /**
     * @var Builder
     */
    protected $builder;
    
    public function name($value)
    {
        return $this->builder->where('name', 'like', '%' . $value . '%');
    }
    
    public function username($value)
    {
        return $this->builder->where('username', 'like', '%' . $value . '%');
    }
    
    public function password($value)
    {
        return $this->builder->where('password', 'like', '%' . $value . '%');
    }
    
    public function hostname($value)
    {
        return $this->builder->where('hostname', 'like', '%' . $value . '%');
    }
    
    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
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
    
    public function domainType($value)
    {
        return $this->builder->where('domain_type', 'like', '%' . $value . '%');
    }
    
    public function status($value)
    {
        return $this->builder->where('status', 'like', '%' . $value . '%');
    }
    
    public function lockPassword($value)
    {
        return $this->builder->where('lock_password', 'like', '%' . $value . '%');
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

    public function isWinrmEnabled($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_winrm_enabled', $value);
    }

    public function isSnapshot($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_snapshot', $value);
    }

    public function isLost($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_lost', $value);
    }

    public function isLocked($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_locked', $value);
    }

    public function isDraft($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_draft', $value);
    }

    public function isTemplate($value)
    {
        if(!is_bool($value)) {
            $value = false;
        }

        return $this->builder->where('is_template', $value);
    }

    public function lastMetadataRequestStart($date)
    {
        return $this->builder->where('last_metadata_request', '>=', $date);
    }

    public function lastMetadataRequestEnd($date)
    {
        return $this->builder->where('last_metadata_request', '<=', $date);
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

    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

    public function iaasComputeMemberId($value)
    {
            $iaasComputeMember = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('uuid', $value)->first();

        if($iaasComputeMember) {
            return $this->builder->where('iaas_compute_member_id', '=', $iaasComputeMember->id);
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

    public function iaasRepositoryImageId($value)
    {
            $iaasRepositoryImage = \NextDeveloper\IAAS\Database\Models\RepositoryImages::where('uuid', $value)->first();

        if($iaasRepositoryImage) {
            return $this->builder->where('iaas_repository_image_id', '=', $iaasRepositoryImage->id);
        }
    }

    public function templateId($value)
    {
            $template = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('uuid', $value)->first();

        if($template) {
            return $this->builder->where('template_id', '=', $template->id);
        }
    }

    public function commonDomainId($value)
    {
            $commonDomain = \NextDeveloper\Commons\Database\Models\Domains::where('uuid', $value)->first();

        if($commonDomain) {
            return $this->builder->where('common_domain_id', '=', $commonDomain->id);
        }
    }

    public function iaasComputePoolId($value)
    {
            $iaasComputePool = \NextDeveloper\IAAS\Database\Models\ComputePools::where('uuid', $value)->first();

        if($iaasComputePool) {
            return $this->builder->where('iaas_compute_pool_id', '=', $iaasComputePool->id);
        }
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE













}
