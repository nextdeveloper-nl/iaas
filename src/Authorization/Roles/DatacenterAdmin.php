<?php

namespace NextDeveloper\IAAS\Authorization\Roles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
            'iam_account_id'    =>  UserHelper::currentAccount()->id
        ]);
    }

    public function getModule()
    {
        return 'iaas';
    }

    public function allowedObjects() :array
    {
        $allowedObjects = (new IaasSuccessManager())->allowedObjects();
        $allowedObjects = array_merge(
            $allowedObjects,
            (new CloudResourceOwner())->allowedObjects()
        );
        $allowedObjects = array_merge(
            $allowedObjects,
            (new CloudNodeAdmin())->allowedObjects()
        );

        return array_merge($allowedObjects, [
            'iaas_datacenters',
            'iaas_cloud_nodes',
            'iaas_compute_members',
            'iaas_compute_pools',
            'iaas_network_pools',
            'iaas_storage_pools',
            'iaas_storage_volumes',
            'iaas_networks',
            'iaas_virtual_machines',
            'iaas_virtual_machine_backups',
            'iaas_ansible_playbooks',
            'iaas_ansible_roles',
            'iaas_ansible_servers',
            'iaas_ansible_system_playbooks',
            'iaas_gateways',
            'iaas_repositories',
            'iaas_repository_images',
            'iaas_dhcp_servers',
            'iaas_ip_addresses',
            'iaas_ip_address_history',
            'iaas_network_members',
            'iaas_network_pools',
            'iaas_storage_members',
            'iaas_virtual_disk_images',
            'iaas_virtual_network_cards',
            'iaas_compute_member_devices',
            'iaas_compute_member_events',
            'iaas_compute_member_network_interfaces',
            'iaas_compute_member_stats'
        ]);
    }

    public function allowedOperations() :array
    {
        $allowedOperations = (new IaasSuccessManager())->allowedOperations();
        $allowedOperations = array_merge(
            $allowedOperations,
            (new CloudResourceOwner())->allowedOperations()
        );
        $allowedOperations = array_merge(
            $allowedOperations,
            (new CloudNodeAdmin())->allowedOperations()
        );

        return array_merge($allowedOperations, [
            'iaas_ansible_playbook_ansible_role:read',
            'iaas_ansible_playbook_ansible_role:create',
            'iaas_ansible_playbook_ansible_role:update',
            'iaas_ansible_playbook_ansible_role:delete',
            'iaas_ansible_playbook_executions:read',
            'iaas_ansible_playbook_executions:create',
            'iaas_ansible_playbook_executions:update',
            'iaas_ansible_playbook_executions:delete',
            'iaas_ansible_playbooks:read',
            'iaas_ansible_playbooks:create',
            'iaas_ansible_playbooks:update',
            'iaas_ansible_playbooks:delete',
            'iaas_ansible_roles:read',
            'iaas_ansible_roles:create',
            'iaas_ansible_roles:update',
            'iaas_ansible_roles:delete',
            'iaas_ansible_servers:read',
            'iaas_ansible_servers:create',
            'iaas_ansible_servers:update',
            'iaas_ansible_servers:delete',
            'iaas_ansible_system_playbook_executions:read',
            'iaas_ansible_system_playbook_executions:create',
            'iaas_ansible_system_playbook_executions:update',
            'iaas_ansible_system_playbook_executions:delete',
            'iaas_ansible_system_playbooks:read',
            'iaas_ansible_system_playbooks:create',
            'iaas_ansible_system_playbooks:update',
            'iaas_ansible_system_playbooks:delete',
            'iaas_ansible_system_plays:read',
            'iaas_ansible_system_plays:create',
            'iaas_ansible_system_plays:update',
            'iaas_ansible_system_plays:delete',
            'iaas_cloud_nodes:read',
            'iaas_cloud_nodes:update',
            'iaas_cloud_nodes:create',
            'iaas_compute_members:read',
            'iaas_compute_members:create',
            'iaas_compute_members:update',
            'iaas_compute_member_devices:read',
            'iaas_compute_member_devices:create',
            'iaas_compute_member_devices:update',
            'iaas_compute_member_devices:delete',
            'iaas_compute_member_events:read',
            'iaas_compute_member_events:create',
            'iaas_compute_member_events:update',
            'iaas_compute_member_events:delete',
            'iaas_compute_member_network_interfaces:read',
            'iaas_compute_member_network_interfaces:create',
            'iaas_compute_member_network_interfaces:update',
            'iaas_compute_member_network_interfaces:delete',
            'iaas_compute_member_stats:read',
            'iaas_compute_member_stats:create',
            'iaas_compute_member_stats:update',
            'iaas_compute_member_stats:delete',
            'iaas_compute_member_storage_volumes:read',
            'iaas_compute_member_storage_volumes:create',
            'iaas_compute_member_storage_volumes:update',
            'iaas_compute_member_storage_volumes:delete',
            'iaas_compute_pools:read',
            'iaas_compute_pools:create',
            'iaas_compute_pools:update',
            'iaas_datacenters:read',
            'iaas_datacenters:create',
            'iaas_datacenters:update',
            'iaas_datacenters:delete',
            'iaas_dhcp_servers:read',
            'iaas_dhcp_servers:create',
            'iaas_dhcp_servers:update',
            'iaas_dhcp_servers:delete',
            'iaas_gateways:read',
            'iaas_gateways:create',
            'iaas_gateways:update',
            'iaas_gateways:delete',
            'iaas_ip_address_history:read',
            'iaas_ip_address_history:create',
            'iaas_ip_address_history:update',
            'iaas_ip_address_history:delete',
            'iaas_ip_addresses:read',
            'iaas_ip_addresses:create',
            'iaas_ip_addresses:update',
            'iaas_ip_addresses:delete',
            'iaas_network_member_devices:read',
            'iaas_network_member_devices:create',
            'iaas_network_member_devices:update',
            'iaas_network_member_devices:delete',
            'iaas_network_members:read',
            'iaas_network_members:create',
            'iaas_network_members:update',
            'iaas_network_members:delete',
            'iaas_network_members_interfaces:read',
            'iaas_network_members_interfaces:create',
            'iaas_network_members_interfaces:update',
            'iaas_network_members_interfaces:delete',
            'iaas_network_pool_stats:read',
            'iaas_network_pool_stats:create',
            'iaas_network_pool_stats:update',
            'iaas_network_pool_stats:delete',
            'iaas_network_stats:read',
            'iaas_network_stats:create',
            'iaas_network_stats:update',
            'iaas_network_stats:delete',
            'iaas_network_pools:read',
            'iaas_network_pools:create',
            'iaas_network_pools:update',
            'iaas_networks:read',
            'iaas_networks:create',
            'iaas_networks:update',
            'iaas_repositories:read',
            'iaas_repositories:create',
            'iaas_repositories:update',
            'iaas_repositories:delete',
            'iaas_repository_images:read',
            'iaas_repository_images:create',
            'iaas_repository_images:update',
            'iaas_repository_images:delete',
            'iaas_storage_pools:read',
            'iaas_storage_pools:create',
            'iaas_storage_pools:update',
            'iaas_storage_volumes:read',
            'iaas_storage_volumes:create',
            'iaas_storage_volumes:update',
            'iaas_storage_member_devices:read',
            'iaas_storage_member_devices:create',
            'iaas_storage_member_devices:update',
            'iaas_storage_member_devices:delete',
            'iaas_storage_member_stats:read',
            'iaas_storage_member_stats:create',
            'iaas_storage_member_stats:update',
            'iaas_storage_member_stats:delete',
            'iaas_storage_members:read',
            'iaas_storage_members:create',
            'iaas_storage_members:update',
            'iaas_storage_members:delete',
            'iaas_storage_volume_stats:read',
            'iaas_storage_volume_stats:create',
            'iaas_storage_volume_stats:update',
            'iaas_storage_volume_stats:delete',
            'iaas_virtual_disk_images:read',
            'iaas_virtual_disk_images:create',
            'iaas_virtual_disk_images:update',
            'iaas_virtual_disk_images:delete',
            'iaas_virtual_machines:read',
            'iaas_virtual_machines:create',
            'iaas_virtual_machines:update',
            'iaas_virtual_machines:delete',
            'iaas_virtual_machine_backups:read',
            'iaas_virtual_machine_backups:create',
            'iaas_virtual_machine_backups:update',
            'iaas_virtual_machine_backups:delete',
            'iaas_virtual_network_card_stats:read',
            'iaas_virtual_network_card_stats:create',
            'iaas_virtual_network_card_stats:update',
            'iaas_virtual_network_card_stats:delete',
            'iaas_virtual_network_cards:read',
            'iaas_virtual_network_cards:create',
            'iaas_virtual_network_cards:update',
            'iaas_virtual_network_cards:delete',
            'iaas_compute_member_network_interfaces:read',
            'iaas_compute_member_network_interfaces:create',
            'iaas_compute_member_network_interfaces:update',
            'iaas_compute_member_network_interfaces:delete',
            'iaas_compute_member_storage_volumes:read',
            'iaas_compute_member_storage_volumes:create',
            'iaas_compute_member_storage_volumes:update',
            'iaas_compute_member_storage_volumes:delete',
        ]);
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
