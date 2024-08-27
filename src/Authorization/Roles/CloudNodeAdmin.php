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

class CloudNodeAdmin extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'cloud-node-admin';

    public const LEVEL = 110;

    public const DESCRIPTION = 'Cloud node admin is the user who has highest level of access to the cloud node that '
        . 'the user and or its organization (company) owns. However this role does not have privilege to list the '
        . 'virtual machines or other resources running on this node.';

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
        /**
         * Here user will be able to list all models, because by default, sales manager can see everybody.
         */
        $builder->where([
            'iam_account_id'    =>  UserHelper::currentAccount()->id
        ]);
    }

    public function checkPrivileges(Users $users = null)
    {
        //return UserHelper::hasRole(self::NAME, $users);
    }

    public function getModule()
    {
        return 'iaas';
    }

    public function allowedOperations() :array
    {
        return [
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
            'iaas_networks:update',
            'iaas_virtual_machines:read',
            'iaas_virtual_machines:create',
            'iaas_virtual_machines:update',
            'iaas_compute_member_network_interfaces:read',
            'iaas_compute_member_network_interfaces:create',
            'iaas_compute_member_network_interfaces:update',
            'iaas_compute_member_network_interfaces:delete',
            'iaas_network_members_interfaces:read',
            'iaas_network_members_interfaces:create',
            'iaas_network_members_interfaces:update',
            'iaas_network_members_interfaces:delete',
            'iaas_ip_addresses:read',
            'iaas_ip_addresses:create',
            'iaas_ip_addresses:update',
            'iaas_ip_addresses:delete',
            'iaas_dhcp_servers:read',
            'iaas_dhcp_servers:create',
            'iaas_dhcp_servers:update',
            'iaas_dhcp_servers:delete',
        ];
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
