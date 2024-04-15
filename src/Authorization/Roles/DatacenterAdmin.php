<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\CRM\Database\Models\AccountManagers;
use NextDeveloper\IAM\Authorization\Roles\AbstractRole;
use NextDeveloper\IAM\Authorization\Roles\IAuthorizationRole;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Helpers\UserHelper;

class DatacenterAdmin extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'datacenter-admin';

    public const LEVEL = 100;

    public const DESCRIPTION = 'Datacenter admin is the role where the user can manage the nodes in datacenter.'
        . 'This role is the highest level of access in the system. However this role does not have privilege to list '
        . 'the virtual machines or other resources running on this node.';

    public const DB_PREFIX = 'iaas';

    /**
     * Applies basic member role sql for Eloquent
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where([
            'iam_account_id'    =>  UserHelper::currentAccount()->id,
            'iam_user_id'       =>  UserHelper::me()->id
        ]);
    }

    public function getModule()
    {
        return 'iaas';
    }

    public function allowedObjects() :array
    {
        return [
            'iaas_datacenters',
            'iaas_cloud_nodes',
            'iaas_compute_members',
            'iaas_compute_pools',
            'iaas_network_pools',
            'iaas_storage_pools',
            'iaas_storage_volumes',
            'iaas_networks',
        ];
    }

    public function allowedOperations() :array
    {
        return [
            'iaas_datacenters:read',
            'iaas_datacenters:update',
            'iaas_datacenters:create',
            'iaas_cloud_nodes:read',
            'iaas_cloud_nodes:update',
            'iaas_cloud_nodes:create',
            'iaas_compute_members:read',
            'iaas_compute_members:create',
            'iaas_compute_members:update',
            'iaas_compute_pools:read',
            'iaas_compute_pools:create',
            'iaas_compute_pools:update',
            'iaas_network_pools:read',
            'iaas_network_pools:create',
            'iaas_network_pools:update',
            'iaas_storage_pools:read',
            'iaas_storage_pools:create',
            'iaas_storage_pools:update',
            'iaas_storage_volumes:read',
            'iaas_storage_volumes:create',
            'iaas_storage_volumes:update',
            'iaas_networks:read',
            'iaas_networks:create',
            'iaas_networks:update'
        ];
    }

    public function checkPrivileges(Users $users = null)
    {
        //return UserHelper::hasRole(self::NAME, $users);
    }

    public function getLevel(): int
    {
        return self::LEVEL;
    }

    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function canBeApplied($column)
    {
        if(self::DB_PREFIX === '*') {
            return true;
        }

        if(Str::startsWith($column, self::DB_PREFIX)) {
            return true;
        }

        return false;
    }

    public function getDbPrefix()
    {
        return self::DB_PREFIX;
    }

    public function checkRules(Users $users): bool
    {
        // TODO: Implement checkRules() method.
    }
}
