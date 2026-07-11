<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Services\AnsibleRolesService;

/**
 * Reconciles the iaas_ansible_roles catalog against the service-role capabilities
 * (capabilities/service-roles/{name}/linux.yml) that actually exist in the pinned
 * toolkit release. Meant to be run after every TOOLKIT_VERSION bump so the catalog
 * never drifts ahead of - or behind - what a config ISO can actually apply.
 */
class SyncServiceRolesCatalog extends Command
{
    /**
     * @var string
     */
    protected $signature = 'iaas:sync-service-roles';

    /**
     * @var string
     */
    protected $description = 'Syncs the iaas_ansible_roles catalog against the service-role capabilities available in the pinned toolkit release.';

    public function handle()
    {
        $result = AnsibleRolesService::syncFromToolkit();

        $this->info('Created: ' . (empty($result['created']) ? '-' : implode(', ', $result['created'])));
        $this->info('Reactivated: ' . (empty($result['reactivated']) ? '-' : implode(', ', $result['reactivated'])));
        $this->info('Deactivated: ' . (empty($result['deactivated']) ? '-' : implode(', ', $result['deactivated'])));
    }
}
