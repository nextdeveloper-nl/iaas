<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\IAM\Authorization\Roles\AbstractRole;
use NextDeveloper\IAM\Authorization\Roles\IAuthorizationRole;
use NextDeveloper\IAM\Database\Models\Users;

class CloudSalesPerson extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'cloud-sales-person';

    public const LEVEL = 150;

    public const DESCRIPTION = 'Cloud sales person can read all IaaS records across tenants and update IaaS account settings to support customers.';

    public const DB_PREFIX = 'iaas';

    public function apply(Builder $builder, Model $model)
    {
        // Cross-tenant: no scope filter applied.
    }

    public function checkUpdatePolicy(Model $model, Users $user): bool
    {
        return in_array($model->getTable() . ':update', $this->allowedOperations(), true);
    }

    public function checkDeletePolicy(Model $model, Users $user): bool
    {
        return false;
    }

    public function getModule()
    {
        return 'iaas';
    }

    public function allowedOperations(): array
    {
        return [
            'iaas_accounts:read',
            'iaas_accounts:update',

            'iaas_storage_pools:read',
            'iaas_storage_pools_perspective:read',
            'iaas_storage_members:read',
            'iaas_storage_members_perspective:read',
            'iaas_storage_volumes:read',
            'iaas_storage_volumes_perspective:read',
            'iaas_compute_member_storage_volumes:read',
            'iaas_compute_member_storage_volumes_perspective:read',
            'iaas_networks:read',

            'iaas_virtual_machines:read',
            'iaas_virtual_machines_perspective:read',
            'iaas_virtual_machines_management_perspective:read',
            'iaas_virtual_disk_images:read',
            'iaas_virtual_network_cards:read',
            'iaas_virtual_network_cards_perspective:read',
            'iaas_virtual_machine_backups:read',
            'iaas_virtual_machine_backups_perspective:read',

            'iaas_ip_gateways:read',
            'iaas_ip_addresses:read',
            'iaas_repositories:read',
            'iaas_repositories_perspective:read',
            'iaas_repository_images:read',
            'iaas_repository_images_perspective:read',

            'iaas_backup_retention_policies:read',
            'iaas_backup_schedules:read',
            'iaas_backup_jobs:read',
            'iaas_backup_job_replications:read',
            'iaas_vm_backup_jobs_perspective:read',

            'iaas_customer_resources_perspective:read',
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
        if (self::DB_PREFIX === '*') {
            return true;
        }

        if (Str::startsWith($column, self::DB_PREFIX)) {
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
        return true;
    }
}
