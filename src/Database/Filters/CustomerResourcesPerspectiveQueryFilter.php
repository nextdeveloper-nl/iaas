<?php

namespace NextDeveloper\IAAS\Database\Filters;

use Illuminate\Database\Eloquent\Builder;
use NextDeveloper\Commons\Database\Filters\AbstractQueryFilter;

/**
 * This class automatically puts where clause on database so that use can filter
 * data returned from the query.
 */
class CustomerResourcesPerspectiveQueryFilter extends AbstractQueryFilter
{
    /**
     * @var Builder
     */
    protected $builder;

    public function accountName($value)
    {
        return $this->builder->where('account_name', 'ilike', '%' . $value . '%');
    }

    //  This is an alias function of accountName
    public function account_name($value)
    {
        return $this->accountName($value);
    }

    public function userName($value)
    {
        return $this->builder->where('user_name', 'ilike', '%' . $value . '%');
    }

    //  This is an alias function of userName
    public function user_name($value)
    {
        return $this->userName($value);
    }

    public function userEmail($value)
    {
        return $this->builder->where('user_email', 'ilike', '%' . $value . '%');
    }

    //  This is an alias function of userEmail
    public function user_email($value)
    {
        return $this->userEmail($value);
    }

    public function resourceType($value)
    {
        return $this->builder->where('resource_type', 'ilike', '%' . $value . '%');
    }

    //  This is an alias function of resourceType
    public function resource_type($value)
    {
        return $this->resourceType($value);
    }

    public function resourceName($value)
    {
        return $this->builder->where('resource_name', 'ilike', '%' . $value . '%');
    }

    //  This is an alias function of resourceName
    public function resource_name($value)
    {
        return $this->resourceName($value);
    }

    public function resourceStatus($value)
    {
        return $this->builder->where('resource_status', 'ilike', '%' . $value . '%');
    }

    //  This is an alias function of resourceStatus
    public function resource_status($value)
    {
        return $this->resourceStatus($value);
    }

    public function isAccountSuspended($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        return $this->builder->whereRaw("is_account_suspended = $bool");
    }

    //  This is an alias function of isAccountSuspended
    public function is_account_suspended($value)
    {
        return $this->isAccountSuspended($value);
    }

    public function isCrmSuspended($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        return $this->builder->whereRaw("is_crm_suspended = $bool");
    }

    //  This is an alias function of isCrmSuspended
    public function is_crm_suspended($value)
    {
        return $this->isCrmSuspended($value);
    }

    public function isCrmDisabled($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        return $this->builder->whereRaw("is_crm_disabled = $bool");
    }

    //  This is an alias function of isCrmDisabled
    public function is_crm_disabled($value)
    {
        return $this->isCrmDisabled($value);
    }

    public function isAccountingDisabled($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        return $this->builder->whereRaw("is_accounting_disabled = $bool");
    }

    //  This is an alias function of isAccountingDisabled
    public function is_accounting_disabled($value)
    {
        return $this->isAccountingDisabled($value);
    }

    public function cpu($value)
    {
        return $this->builder->where('cpu', $value);
    }

    public function ram($value)
    {
        return $this->builder->where('ram', $value);
    }

    public function iamAccountId($value)
    {
        $account = \NextDeveloper\IAM\Database\Models\Accounts::findByRef($value);

        if ($account) {
            return $this->builder->where('iam_account_id', $account->id);
        }

        return $this->builder->where('iam_account_id', null);
    }

    public function iaasCloudNodeId($value)
    {
        $cloudNode = \NextDeveloper\IAAS\Database\Models\CloudNodes::findByRef($value);

        if ($cloudNode) {
            return $this->builder->where('iaas_cloud_node_id', $cloudNode->id);
        }

        return $this->builder->where('iaas_cloud_node_id', null);
    }

    //  This is an alias function of iaasCloudNodeId
    public function iaas_cloud_node_id($value)
    {
        return $this->iaasCloudNodeId($value);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
