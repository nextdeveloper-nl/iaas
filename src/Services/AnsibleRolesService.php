<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\AnsibleRoles;
use NextDeveloper\IAAS\Database\Models\AnsibleServers;
use NextDeveloper\IAAS\Exceptions\UnknownServiceRoleException;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractAnsibleRolesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for AnsibleRoles
 *
 * Class AnsibleRolesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AnsibleRolesService extends AbstractAnsibleRolesService
{
    //  iaas_ansible_roles.iaas_ansible_server_id is NOT NULL - a legacy requirement from the old
    //  design where a role was always executed against a real SSH-reachable control node
    //  (iaas_ansible_servers). Service roles installed via the toolkit have no such node - they
    //  run locally on the VM itself during first boot (see ToolkitService) - so this placeholder
    //  row exists purely to satisfy that FK and is never actually connected to or executed against.
    private const LOCAL_EXECUTION_SERVER_NAME = 'toolkit-local-execution';

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    /**
     * Resolves the service roles requested for a VM (e.g. ['docker' => ['config' => [...]], 'postgresql' => []])
     * against the iaas_ansible_roles catalog - each requested role name must match an active catalog entry's
     * `name` (which is also the toolkit capability folder name, capabilities/service-roles/{name}/linux.yml).
     *
     * Returns the normalized shape stored in the VM's features.service_roles and later consumed by
     * VirtualMachinesMetadataService/ToolkitService: ['docker' => ['enabled' => true, 'config' => [...merged]]].
     *
     * @throws UnknownServiceRoleException if a requested role does not exist or is not active.
     */
    public static function resolveForVirtualMachine(array $requested): array
    {
        $resolved = [];

        foreach ($requested as $name => $override) {
            $override = is_array($override) ? $override : [];

            //  The service role catalog is shared platform-wide, not account-scoped (there's no
            //  is_public column on this table for MemberRole's scope to key off), so this has to
            //  bypass AuthorizationScope or every non-admin account's VM create/update would 404
            //  on every role - see VirtualMachinesMetadataService::collectServiceRoles() which
            //  already does the same for the read-back path.
            $role = AnsibleRoles::withoutGlobalScope(AuthorizationScope::class)
                ->where('name', $name)
                ->where('is_active', true)
                ->first();

            if (!$role) {
                throw new UnknownServiceRoleException(
                    "Service role [{$name}] does not exist or is not active."
                );
            }

            $defaultConfig = is_array($role->config) ? $role->config : [];
            $overrideConfig = is_array($override['config'] ?? null) ? $override['config'] : [];

            $resolved[$name] = [
                'enabled' => true,
                'config' => array_merge($defaultConfig, $overrideConfig),
            ];
        }

        return $resolved;
    }

    /**
     * Reconciles the iaas_ansible_roles catalog against the service-role capabilities that
     * actually exist in the pinned toolkit release: creates a catalog entry for any capability
     * folder that doesn't have one yet, reactivates one that was previously deactivated, and
     * deactivates (never deletes - VMs may still reference it in features.service_roles) any
     * catalog entry whose capability folder no longer exists in the pinned release.
     *
     * Meant to be run after every toolkit version bump - see the iaas:sync-service-roles command.
     *
     * @return array{created: string[], reactivated: string[], deactivated: string[]}
     */
    public static function syncFromToolkit(): array
    {
        //  Catalog rows are created/updated under the platform account, same as any other
        //  system-driven write with no authenticated request behind it (see SynchronizeIsos).
        UserHelper::setAdminAsCurrentUser();

        $discovered = ToolkitService::discoverServiceRoleNames();

        //  Laravel compiles whereNotIn('name', []) as "match everything" - refuse to run the
        //  deactivation pass on an empty discovery (e.g. toolkit release not cached yet) instead
        //  of deactivating the entire catalog by accident.
        if (empty($discovered)) {
            return ['created' => [], 'reactivated' => [], 'deactivated' => []];
        }

        $discoveredNames = array_keys($discovered);

        $created = [];
        $reactivated = [];
        $deactivated = [];

        $existingRoles = AnsibleRoles::withoutGlobalScope(AuthorizationScope::class)
            ->whereIn('name', $discoveredNames)
            ->get()
            ->keyBy('name');

        foreach ($discovered as $name => $hash) {
            $role = $existingRoles->get($name);

            if (!$role) {
                self::create([
                    'name' => $name,
                    'config' => [],
                    'hash' => $hash,
                    'is_active' => true,
                    'iaas_ansible_server_id' => self::resolveLocalExecutionServerId(),
                ]);

                $created[] = $name;

                continue;
            }

            $updates = [];

            if (!$role->is_active) {
                $updates['is_active'] = true;
                $reactivated[] = $name;
            }

            if ($role->hash !== $hash) {
                $updates['hash'] = $hash;
            }

            if (!empty($updates)) {
                self::update($role->uuid, $updates);
            }
        }

        $staleRoles = AnsibleRoles::withoutGlobalScope(AuthorizationScope::class)
            ->where('is_active', true)
            ->whereNotIn('name', $discoveredNames)
            ->get();

        foreach ($staleRoles as $role) {
            self::update($role->uuid, ['is_active' => false]);

            $deactivated[] = $role->name;
        }

        return [
            'created' => $created,
            'reactivated' => $reactivated,
            'deactivated' => $deactivated,
        ];
    }

    /**
     * Finds (or creates once) the placeholder iaas_ansible_servers row used to satisfy
     * iaas_ansible_roles.iaas_ansible_server_id for toolkit-driven service roles - see the
     * constant's docblock for why this exists.
     */
    private static function resolveLocalExecutionServerId(): int
    {
        $server = AnsibleServers::withoutGlobalScope(AuthorizationScope::class)
            ->where('name', self::LOCAL_EXECUTION_SERVER_NAME)
            ->first();

        if ($server) {
            return $server->id;
        }

        $server = AnsibleServersService::create([
            'name' => self::LOCAL_EXECUTION_SERVER_NAME,
            'is_external_machine' => false,
            'is_active' => false,
            'is_public' => false,
        ]);

        return $server->id;
    }
}