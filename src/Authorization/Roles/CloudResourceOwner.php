<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Exceptions\MustHaveNIN;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\CRM\Database\Models\AccountManagers;
use NextDeveloper\IAAS\Authorization\Rules\ServiceAvailability\TurkishMustHaveNIN;
use NextDeveloper\IAM\Authorization\Roles\AbstractRole;
use NextDeveloper\IAM\Authorization\Roles\IAuthorizationRole;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Helpers\UserHelper;

class CloudResourceOwner extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'cloud-resource-owner';

    public const LEVEL = 150;

    public const DESCRIPTION = 'This is the role for the user who creates and manages the related resources.';

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
        $builder->where('iam_account_id', UserHelper::currentAccount()->id);
    }

    public function checkPrivileges(Users $users = null)
    {
        //return UserHelper::hasRole(self::NAME, $users);
    }

    public function checkRules(Users $users): bool
    {
        if(!TurkishMustHaveNIN::can($users)) { throw new MustHaveNIN(); }

        return true;
    }

    public function getModule()
    {
        return 'iaas';
    }

    public function allowedOperations() :array
    {
        return [
            'iaas_datacenters:read',
            'iaas_cloud_nodes:read',
            'iaas_compute_members:read',
            'iaas_compute_pools:read',
            'iaas_network_pools:read',
            'iaas_storage_pools:read',
            'iaas_storage_volumes:read',
            'iaas_networks:read',
            'iaas_networks:create',
            'iaas_networks:update',
            'iaas_networks:delete',
            'iaas_virtual_machines:read',
            'iaas_virtual_machines:create',
            'iaas_virtual_machines:update',
            'iaas_virtual_machines:delete',
            'iaas_virtual_machine_backups:read',
            'iaas_virtual_machine_backups:create',
            'iaas_virtual_machine_backups:update',
            'iaas_virtual_machine_backups:delete',
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
}
