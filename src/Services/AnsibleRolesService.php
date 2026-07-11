<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Models\AnsibleRoles;
use NextDeveloper\IAAS\Exceptions\UnknownServiceRoleException;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractAnsibleRolesService;

/**
 * This class is responsible from managing the data for AnsibleRoles
 *
 * Class AnsibleRolesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AnsibleRolesService extends AbstractAnsibleRolesService
{

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

            $role = AnsibleRoles::where('name', $name)
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
}