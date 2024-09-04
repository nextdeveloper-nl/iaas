<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;


/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class VirtualMahinesPerspectiveQueryFilter extends AbstractQueryFilter
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

    public function description($value)
    {
        return $this->builder->where('description', 'like', '%' . $value . '%');
    }

    public function hostname($value)
    {
        return $this->builder->where('hostname', 'like', '%' . $value . '%');
    }

    public function username($value)
    {
        return $this->builder->where('username', 'like', '%' . $value . '%');
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

    public function cloudNode($value)
    {
        return $this->builder->where('cloud_node', 'like', '%' . $value . '%');
    }

    public function domain($value)
    {
        return $this->builder->where('domain', 'like', '%' . $value . '%');
    }

    public function maintainer($value)
    {
        return $this->builder->where('maintainer', 'like', '%' . $value . '%');
    }

    public function responsible($value)
    {
        return $this->builder->where('responsible', 'like', '%' . $value . '%');
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

    public function diskCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('disk_count', $operator, $value);
    }

    public function networkCardCount($value)
    {
        $operator = substr($value, 0, 1);

        if ($operator != '<' || $operator != '>') {
            $operator = '=';
        } else {
            $value = substr($value, 1);
        }

        return $this->builder->where('network_card_count', $operator, $value);
    }

    public function isTemplate($value)
    {


        return $this->builder->where('is_template', $value);
    }

    public function isDraft($value)
    {


        return $this->builder->where('is_draft', $value);
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

    public function commonDomainId($value)
    {
            $commonDomain = \NextDeveloper\Commons\Database\Models\Domains::where('uuid', $value)->first();

        if($commonDomain) {
            return $this->builder->where('common_domain_id', '=', $commonDomain->id);
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
