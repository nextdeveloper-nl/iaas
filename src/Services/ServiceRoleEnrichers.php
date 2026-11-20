<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Contracts\ServiceRoleEnricherInterface;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;

/**
 * Registry of service role enrichers (see ServiceRoleEnricherInterface).
 *
 * Applied only when a configuration ISO is built, never by the plain metadata
 * read: an enricher may issue a one-time secret, and reading a machine's metadata
 * must not issue or rotate one. The enriched values go onto the ISO and nowhere
 * else; they are never written back to the machine's features.
 */
class ServiceRoleEnrichers
{
    /**
     * @var array<string, class-string<ServiceRoleEnricherInterface>>
     */
    private static array $enrichers = [];

    /**
     * @param class-string<ServiceRoleEnricherInterface> $enricherClass
     */
    public static function register(string $roleName, string $enricherClass): void
    {
        self::$enrichers[$roleName] = $enricherClass;
    }

    public static function forget(string $roleName): void
    {
        unset(self::$enrichers[$roleName]);
    }

    /**
     * Returns the given service roles with each enabled role's config extended by
     * its enricher. A failing enricher is logged and skipped, so one role's problem
     * never stops the ISO from being built.
     *
     * @param array<string, array{enabled?: bool, config?: array<string, mixed>}> $serviceRoles
     * @return array<string, array{enabled?: bool, config?: array<string, mixed>}>
     */
    public static function apply(VirtualMachines $vm, array $serviceRoles): array
    {
        foreach ($serviceRoles as $name => $role) {
            if (!isset(self::$enrichers[$name]) || !is_array($role) || empty($role['enabled'])) {
                continue;
            }

            $config = is_array($role['config'] ?? null) ? $role['config'] : [];

            try {
                $enricher = app(self::$enrichers[$name]);

                if (!$enricher instanceof ServiceRoleEnricherInterface) {
                    throw new \LogicException(self::$enrichers[$name] . ' does not implement ServiceRoleEnricherInterface.');
                }

                $extra = $enricher->enrich($vm, $config);
            } catch (\Throwable $e) {
                Log::warning("[ServiceRoleEnrichers@apply] Enricher for service role [{$name}] failed for VM [{$vm->uuid}]: " . $e->getMessage());

                continue;
            }

            if (!empty($extra)) {
                $serviceRoles[$name]['config'] = array_merge($config, $extra);
            }
        }

        return $serviceRoles;
    }
}
