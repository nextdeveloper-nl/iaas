<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Helpers\DatabaseHelper;
use NextDeveloper\CRM\Database\Models\AccountManagers;
use NextDeveloper\IAM\Authorization\Roles\AbstractRole;
use NextDeveloper\IAM\Authorization\Roles\IAuthorizationRole;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Helpers\UserHelper;

class CloudSalesPerson extends AbstractRole implements IAuthorizationRole
{
    public const NAME = 'cloud-sales-person';

    public const LEVEL = 140;

    public const DESCRIPTION = 'Cloud sales person can read all IaaS records across tenants and update IaaS account settings to support customers.';

    public const DB_PREFIX = 'iaas';

    public function apply(Builder $builder, Model $model)
    {
        // Mirror visibility of /crm/accounts-perspective: sales-admin / sales-manager-admin
        // can see everything, so cloud-sales-person on the same user gets the same reach.
        if (
            (
                UserHelper::hasRole('sales-admin') ||
                UserHelper::hasRole('sales-manager-admin')
            )
            &&
            (
                $model->getTable() === 'iaas_accounts' ||
                $model->getTable() === 'iaas_accounts_perspective'
            )
        ) {
            return;
        }

        $accountId = UserHelper::currentAccount()->id;

        $managedIamAccountsSql = '(
            select ca.iam_account_id from crm_accounts ca
            join crm_account_managers cam on cam.crm_account_id = ca.id
            where cam.iam_account_id = ' . $accountId . '
        )';

        if ($model->getTable() === 'iaas_accounts') {
            $builder->whereRaw('iam_account_id IN ' . $managedIamAccountsSql);

            return;
        }

        if (DatabaseHelper::isColumnExists($model->getTable(), 'iam_account_id')) {
            $builder->whereRaw('iam_account_id IN ' . $managedIamAccountsSql);
        }
    }

    public function checkUpdatePolicy(Model $model, Users $user): bool
    {
        if (!in_array($model->getTable() . ':update', $this->allowedOperations(), true)) {
            return false;
        }

        if (!isset($model->iam_account_id)) {
            return false;
        }

        return AccountManagers::withoutGlobalScopes()
            ->where('iam_account_id', UserHelper::currentAccount()->id)
            ->whereIn('crm_account_id', function ($q) use ($model) {
                $q->select('id')
                    ->from('crm_accounts')
                    ->where('iam_account_id', $model->iam_account_id);
            })
            ->exists();
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
            'iaas_accounts:create',
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
