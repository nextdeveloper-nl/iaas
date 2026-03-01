<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Exceptions\MustHaveNIN;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
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
            'iaas_vm_backup_heatmaps:read',
            'iaas_kpi_performance:read',
            'iaas_active_alarms_perspective:read',
            'iaas_vm_hourly_stats:read',
            'iaas_account_hourly_stats:read',
            'iaas_account_current_stats:read',
            'iaas_vm_daily_stats:read',
            'iaas_vm_backup_jobs_perspective:read',
            'iaas_vm_backup_stats:read',

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
            'iaas_virtual_machines:restore',

            'iaas_virtual_disk_images:read',
            'iaas_virtual_disk_images:create',
            'iaas_virtual_disk_images:update',
            'iaas_virtual_disk_images:delete',
            'iaas_virtual_disk_images:restore',

            'iaas_virtual_network_cards:read',
            'iaas_virtual_network_cards:create',
            'iaas_virtual_network_cards:update',
            'iaas_virtual_network_cards:delete',
            'iaas_virtual_network_cards:restore',

            'iaas_virtual_machine_backups:read',
            'iaas_virtual_machine_backups:create',
            'iaas_virtual_machine_backups:update',
            'iaas_virtual_machine_backups:delete',
            'iaas_virtual_machine_backups:restore',

            'iaas_ip_gateways:read',
            'iaas_ip_gateways:create',
            'iaas_ip_gateways:update',
            'iaas_ip_gateways:delete',
            'iaas_ip_addresses:read',
            'iaas_ip_addresses:create',
            'iaas_ip_addresses:update',
            'iaas_ip_addresses:delete',

            'iaas_repositories:read',
            'iaas_repositories:create',
            'iaas_repositories:update',
            'iaas_repositories:delete',

            'iaas_repository_images:read',
            'iaas_repository_images:create',
            'iaas_repository_images:update',
            'iaas_repository_images:delete',

            'iaas_gateways:read',
            'iaas_gateways:create',
            'iaas_gateways:update',
            'iaas_gateways:delete',

            'iaas_backup_retention_policies:read',
            'iaas_backup_retention_policies:create',
            'iaas_backup_retention_policies:update',
            'iaas_backup_retention_policies:delete',

            'iaas_backup_schedules:read',
            'iaas_backup_schedules:create',
            'iaas_backup_schedules:update',
            'iaas_backup_schedules:delete',

            'iaas_backup_jobs:read',
            'iaas_backup_jobs:create',
            'iaas_backup_jobs:update',
            'iaas_backup_jobs:delete',
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
