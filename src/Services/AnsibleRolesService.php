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

            //  role->config is the catalog shape - `{key: {default, description}}` - not what a VM's
            //  Ansible run reads, so this flattens to `{key: default}` before merging in the
            //  customer's override. Without this, the description text would ride along into
            //  service_roles.<name>.config.* and get templated straight into config files.
            $defaultConfig = [];

            foreach ((is_array($role->config) ? $role->config : []) as $key => $spec) {
                $defaultConfig[$key] = is_array($spec) ? ($spec['default'] ?? null) : $spec;
            }

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
     * folder that doesn't have one yet (seeded with its scanned config schema - see
     * ToolkitService::discoverServiceRoleNames()), reactivates one that was previously
     * deactivated, overwrites an existing entry's config with whatever the pinned release's
     * defaults.yml currently says (config isn't meant to be hand-edited between syncs - it's a
     * scanned mirror of the toolkit, not a per-role override store), and deactivates (never
     * deletes - VMs may still reference it in features.service_roles) any catalog entry whose
     * capability folder no longer exists in the pinned release.
     *
     * Meant to be run after every toolkit version bump - see the iaas:sync-service-roles command.
     *
     * @return array{created: string[], updated: string[], reactivated: string[], deactivated: string[]}
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
            return ['created' => [], 'updated' => [], 'reactivated' => [], 'deactivated' => []];
        }

        $discoveredNames = array_keys($discovered);

        $created = [];
        $updated = [];
        $reactivated = [];
        $deactivated = [];

        $existingRoles = AnsibleRoles::withoutGlobalScope(AuthorizationScope::class)
            ->whereIn('name', $discoveredNames)
            ->get()
            ->keyBy('name');

        foreach ($discovered as $name => $meta) {
            $role = $existingRoles->get($name);

            if (!$role) {
                self::create([
                    'name' => $name,
                    'config' => $meta['config'],
                    'hash' => $meta['hash'],
                    'description' => $meta['description'],
                    'is_active' => true,
                    'iaas_ansible_server_id' => self::resolveLocalExecutionServerId(),
                ]);

                $created[] = $name;

                continue;
            }

            $updates = [];
            $contentChanged = false;

            if (!$role->is_active) {
                $updates['is_active'] = true;
                $reactivated[] = $name;
            }

            if ($role->hash !== $meta['hash']) {
                $updates['hash'] = $meta['hash'];
                $contentChanged = true;
            }

            if ($role->description !== $meta['description']) {
                $updates['description'] = $meta['description'];
                $contentChanged = true;
            }

            //  Config always mirrors the pinned toolkit's defaults.yml verbatim, so a default
            //  value changed in a new release reaches existing roles the same way a hash/
            //  description change does - it's a scanned schema, not a per-role override store.
            if ($role->config != $meta['config']) {
                $updates['config'] = $meta['config'];
                $contentChanged = true;
            }

            if ($contentChanged) {
                $updated[] = $name;
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
            'updated' => $updated,
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