<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Exceptions\MustHaveNIN;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
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
        if(
            $model->getTable() == 'iaas_compute_pools' ||
            $model->getTable() == 'iaas_compute_pools_perspective' ||
            $model->getTable() == 'iaas_networks' ||
            $model->getTable() == 'iaas_networks_perspective'
        ) {
            $builder->where('iam_account_id', UserHelper::currentAccount()->id)
                ->orWhere('is_public', true);
        }

        $isPublicExists = DatabaseHelper::isColumnExists($model->getTable(), 'is_public');

        /**
         * Here user will be able to list all models, because by default, sales manager can see everybody.
         */
        $builder->where('iam_account_id', UserHelper::currentAccount()->id);

        if($isPublicExists)
            $builder->orWhere('is_public', true);
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

    public function checkUpdatePolicy(Model $model, Users $user): bool
    {
        return true;
    }

    public function allowedOperations() :array
    {
        return [
            'iaas_accounts:read',

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

            'iaas_virtual_machines_perspective:read',

            'iaas_virtual_machines:read',
            'iaas_virtual_machines:create',
            'iaas_virtual_machines:update',
            'iaas_virtual_machines:delete',
            'iaas_virtual_disk_images:read',
            'iaas_virtual_disk_images:create',
            'iaas_virtual_disk_images:update',
            'iaas_virtual_disk_images:delete',
            'iaas_virtual_network_cards:read',
            'iaas_virtual_network_cards:create',
            'iaas_virtual_network_cards:update',
            'iaas_virtual_network_cards:delete',
            'iaas_virtual_machine_backups:read',
            'iaas_virtual_machine_backups:create',
            'iaas_virtual_machine_backups:update',
            'iaas_virtual_machine_backups:delete',

            'iaas_ip_gateways:read',
            'iaas_ip_gateways:create',
            'iaas_ip_gateways:update',
            'iaas_ip_gateways:delete',
            'iaas_ip_addresses:read',
            'iaas_ip_addresses:create',
            'iaas_ip_addresses:update',
            'iaas_ip_addresses:delete',
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
