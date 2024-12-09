<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;


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
        return $this->builder->where('name', 'ilike', '%' . $value . '%');
    }


    public function username($value)
    {
        return $this->builder->where('username', 'ilike', '%' . $value . '%');
    }


    public function password($value)
    {
        return $this->builder->where('password', 'ilike', '%' . $value . '%');
    }


    public function hostname($value)
    {
        return $this->builder->where('hostname', 'ilike', '%' . $value . '%');
    }


    public function description($value)
    {
        return $this->builder->where('description', 'ilike', '%' . $value . '%');
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


    public function domainType($value)
    {
        return $this->builder->where('domain_type', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of domainType
    public function domain_type($value)
    {
        return $this->domainType($value);
    }

    public function status($value)
    {
        return $this->builder->where('status', 'ilike', '%' . $value . '%');
    }


    public function lockPassword($value)
    {
        return $this->builder->where('lock_password', 'ilike', '%' . $value . '%');
    }

        //  This is an alias function of lockPassword
    public function lock_password($value)
    {
        return $this->lockPassword($value);
    }

    public function autoBackupInterval($value)
    {
        return $this->builder->where('auto_backup_interval', 'like', '%' . $value . '%');
    }

        //  This is an alias function of autoBackupInterval
    public function auto_backup_interval($value)
    {
        return $this->autoBackupInterval($value);
    }

    public function autoBackupTime($value)
    {
        return $this->builder->where('auto_backup_time', 'like', '%' . $value . '%');
    }

        //  This is an alias function of autoBackupTime
    public function auto_backup_time($value)
    {
        return $this->autoBackupTime($value);
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

    //  This is an alias function of snapshotOfVirtualMachine
    public function snapshot_of_virtual_machine($value)
    {
        return $this->snapshotOfVirtualMachine($value);
    }

    public function isWinrmEnabled($value)
    {
        return $this->builder->where('is_winrm_enabled', $value);
    }

        //  This is an alias function of isWinrmEnabled
    public function is_winrm_enabled($value)
    {
        return $this->isWinrmEnabled($value);
    }

    public function isSnapshot($value)
    {
        return $this->builder->where('is_snapshot', $value);
    }

        //  This is an alias function of isSnapshot
    public function is_snapshot($value)
    {
        return $this->isSnapshot($value);
    }

    public function isLost($value)
    {
        return $this->builder->where('is_lost', $value);
    }

        //  This is an alias function of isLost
    public function is_lost($value)
    {
        return $this->isLost($value);
    }

    public function isLocked($value)
    {
        return $this->builder->where('is_locked', $value);
    }

        //  This is an alias function of isLocked
    public function is_locked($value)
    {
        return $this->isLocked($value);
    }

    public function isDraft($value)
    {
        return $this->builder->where('is_draft', $value);
    }

        //  This is an alias function of isDraft
    public function is_draft($value)
    {
        return $this->isDraft($value);
    }

    public function isTemplate($value)
    {
        return $this->builder->where('is_template', $value);
    }

        //  This is an alias function of isTemplate
    public function is_template($value)
    {
        return $this->isTemplate($value);
    }

    public function lastMetadataRequestStart($date)
    {
        return $this->builder->where('last_metadata_request', '>=', $date);
    }

    public function lastMetadataRequestEnd($date)
    {
        return $this->builder->where('last_metadata_request', '<=', $date);
    }

    //  This is an alias function of lastMetadataRequest
    public function last_metadata_request_start($value)
    {
        return $this->lastMetadataRequestStart($value);
    }

    //  This is an alias function of lastMetadataRequest
    public function last_metadata_request_end($value)
    {
        return $this->lastMetadataRequestEnd($value);
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

    public function iaasCloudNodeId($value)
    {
            $iaasCloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('uuid', $value)->first();

        if($iaasCloudNode) {
            return $this->builder->where('iaas_cloud_node_id', '=', $iaasCloudNode->id);
        }
    }

        //  This is an alias function of iaasCloudNode
    public function iaas_cloud_node_id($value)
    {
        return $this->iaasCloudNode($value);
    }

    public function iaasComputeMemberId($value)
    {
            $iaasComputeMember = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('uuid', $value)->first();

        if($iaasComputeMember) {
            return $this->builder->where('iaas_compute_member_id', '=', $iaasComputeMember->id);
        }
    }

        //  This is an alias function of iaasComputeMember
    public function iaas_compute_member_id($value)
    {
        return $this->iaasComputeMember($value);
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


    public function templateId($value)
    {
            $template = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('uuid', $value)->first();

        if($template) {
            return $this->builder->where('template_id', '=', $template->id);
        }
    }

        //  This is an alias function of template
    public function template_id($value)
    {
        return $this->template($value);
    }

    public function commonDomainId($value)
    {
            $commonDomain = \NextDeveloper\Commons\Database\Models\Domains::where('uuid', $value)->first();

        if($commonDomain) {
            return $this->builder->where('common_domain_id', '=', $commonDomain->id);
        }
    }

        //  This is an alias function of commonDomain
    public function common_domain_id($value)
    {
        return $this->commonDomain($value);
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

    public function iaasComputePoolId($value)
    {
            $iaasComputePool = \NextDeveloper\IAAS\Database\Models\ComputePools::where('uuid', $value)->first();

        if($iaasComputePool) {
            return $this->builder->where('iaas_compute_pool_id', '=', $iaasComputePool->id);
        }
    }

    //  This is an alias function of iaasComputePool
    public function iaas_compute_pool_id($value)
    {
        return $this->iaasComputePool($value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public function snapshotOfVirtualMachine($uuid): Builder
    {
        $vm = VirtualMachines::where('uuid', $uuid)
            ->first();

        if($vm)
            return $this->builder->where('snapshot_of_virtual_machine', $vm->id);
        else
            return $this->builder->where('id', '=', '0');
    }
}
