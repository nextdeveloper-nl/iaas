<?php

namespace NextDeveloper\IAAS\Services;

use App\Helpers\Http\ResponseHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Exceptions\NotFoundException;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Database\Models\AnsibleRoles;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Database\Models\EnvVarGroupVars;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Database\Models\IpAddresses;
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\IAAS\Database\Models\SshPublicKeyVirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineEnvVarGroups;
use NextDeveloper\IAAS\Database\Models\VirtualMachineEnvVars;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAM\Database\Models\SshPublicKeys;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotCreateVirtualMachine;
use NextDeveloper\IAAS\Exceptions\CannotUpdateResourcesException;
use NextDeveloper\IAAS\Helpers\IaasHelper;
use NextDeveloper\IAAS\Helpers\ResourceCalculationHelper;
use NextDeveloper\IAAS\ResourceLimiters\SimpleLimiter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for VirtualMachines
 *
 * Class VirtualMachinesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachinesMetadataService extends AbstractVirtualMachinesService
{
    public static function getMetadata(VirtualMachines $vm) : array
    {
        if(!$vm) {
            return [
                'error' =>  'Virtual machine not found. Please provide a valid virtual machine instance.'
            ];
        }

        $vm = VirtualMachinesService::fixHostname($vm);

        $vdis = VirtualMachinesService::getVirtualDiskImages($vm);
        $vifs = VirtualMachinesService::getVirtualNetworkCards($vm);

        $diskConfiguration = [];

        foreach ($vdis as $vdi) {
            $diskConfiguration[] = [
                'disk_type'     => $vdi->is_cdrom ? 'cdrom' : 'user',
                'device_number' => $vdi->device_number,
                'total_disk'    => $vdi->size,
            ];
        }

        $vifConfiguration = [];

        foreach ($vifs as $vif) {
            $network = self::buildNetworkData($vif->iaas_network_id);

            $data = [
                'device_number' => $vif->device_number,
                'mac_addr'      => $vif->mac_addr,
                'network_name'  => $network['name'] ?? null,
                'network'       => $network,
            ];

            $ips = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_network_card_id', $vif->id)
                ->get();

            $data['ip_list'] = [
                'data' => $ips->map(function ($ip) {
                    return [
                        'id'          => $ip->id,
                        'ip_addr'     => $ip->ip_addr,
                        'is_reserved' => $ip->is_reserved,
                    ];
                }),
            ];

            $vifConfiguration[] = $data;
        }

        $computePool = VirtualMachinesService::getComputePool($vm);
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        //  hypervisor_type/hypervisor_version aren't real columns; ComputeMembers only
        //  stores a combined "<Type> <version>" string (e.g. "XenServer 8.2.5")
        $hypervisorType = null;
        $hypervisorVersion = null;

        if ($computeMember?->hypervisor_model) {
            [$hypervisorType, $hypervisorVersion] = array_pad(
                explode(' ', $computeMember->hypervisor_model, 2),
                2,
                null
            );
        }

        $computePoolArray = [
            'id' => $computePool->uuid,
            'name' => $computePool->name,
            'pool_type' => $computePool->pool_type,
            'hypervisor_type' => $hypervisorType,
            'hypervisor_version' => $hypervisorVersion,
        ];

        $cloudNode = VirtualMachinesService::getCloudPool($vm);
        $cloudPoolArray = [
            'id' => $cloudNode->uuid,
            'name' => $cloudNode->name,
            'location' => $cloudNode->location,
            'provider' => $cloudNode->provider,
            'region' => $cloudNode->region,
        ];

        //  We need to make the username fix
        //  we need to make the hostname fix

        return [
            'hostname' => $vm->hostname,
            'username' => $vm->username,
            'password' => VirtualMachinesService::getRawPassword($vm),
            'virtual_machine_id' => $vm->uuid,
            'agent_api_key' => $vm->agent_api_key,
            'virtual_disks' => $diskConfiguration,
            'virtual_network_cards' => $vifConfiguration,
            'service_roles' => self::collectServiceRoles($vm),
            'compute_pool' => $computePoolArray,
            'cloud_node' => $cloudPoolArray,
            'ssh_keys' => self::collectSshKeys($vm),
            'env_vars' => self::collectEnvVars($vm),
            'tokens'   => $vm->tokens ?? [],
            'agent'    => self::buildAgentConfiguration($vm),
        ];
    }

    /**
     * Single source of truth for the PlusClouds agent's own config (agent.yaml on the
     * config ISO). NATS endpoints are pulled from the same config the platform's own
     * NatsService uses, so the agent can never drift from what the platform connects to.
     */
    private static function buildAgentConfiguration(VirtualMachines $vm) : array
    {
        return [
            'nats' => [
                'connection_type' => 'websocket',
                'url'             => 'nats://' . config('events.nats.server_host') . ':' . config('events.nats.server_port'),
                'websocket_url'   => 'wss://' . config('events.nats.host') . ':' . config('events.nats.port'),
                'agent_uuid'      => $vm->uuid,
                'api_key'         => $vm->agent_api_key,
                'max_reconnects'  => -1,
                'reconnect_wait'  => '5s',
            ],
            'agent' => [
                'heartbeat_interval' => '30s',
                'telemetry_interval' => '30s',
                'allowed_operations' => [
                    'agent.allowed_operations',
                    'services.list',
                    'services.get',
                    'services.start',
                    'services.stop',
                    'services.restart',
                    'services.reload',
                    'services.enable',
                    'services.disable',
                    'system.info',
                    'system.metrics',
                    'system.cpu',
                    'system.memory',
                    'system.disk',
                    'system.network',
                    'system.update',
                    'telemetry.set_interval',
                ],
                'allowed_commands' => [
                    '/usr/bin/journalctl',
                    '/usr/bin/df',
                    '/usr/bin/free',
                ],
            ],
            'iso' => [
                'mount_path' => '/media/plusclouds-config',
            ],
            'log' => [
                'level'  => 'debug',
                'format' => 'console',
                'file'   => '/var/log/plusclouds/agent.log',
            ],
            'autoheal' => [
                'enabled'       => true,
                'restart_delay' => '10s',
            ],
        ];
    }

    public static function getAgentYaml(VirtualMachines $vm) : string
    {
        $yaml = yaml_emit(self::buildAgentConfiguration($vm), YAML_UTF8_ENCODING, YAML_LN_BREAK);

        $yaml = preg_replace('/^---\s*\n/', '', $yaml);
        $yaml = preg_replace('/\n\.\.\.\s*$/', '', $yaml);

        $header = "# PlusClouds Agent v2 Configuration\n" .
            "#\n" .
            "# This file is the primary source of identity for the agent. In production it\n" .
            "# is written by the platform during VM provisioning (via the ISO config drive)\n" .
            "# and the ISO is unmounted before the agent starts. All values here can also be\n" .
            "# overridden by environment variables (prefix: PLUSCLOUDS_AGENT_).\n\n";

        return $header . $yaml;
    }

    private static function buildNetworkData(?int $networkId) : array
    {
        if (!$networkId) {
            return [];
        }

        $network = Networks::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $networkId)
            ->first();

        if (!$network) {
            return [];
        }

        $gatewayIp = null;
        $subnet = null;
        $netmask = null;
        $networkAddr = null;

        if ($network->cidr) {
            [$networkAddr, $prefix] = explode('/', $network->cidr, 2);
            $prefix = (int) $prefix;
            $netmask = long2ip(-1 << (32 - $prefix));
            $subnet = (string) $prefix;
        }

        if ($network->iaas_gateway_id) {
            $gateway = Gateways::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $network->iaas_gateway_id)
                ->first();

            $gatewayIp = $gateway?->ip_addr;
        }

        $dhcpServerIp = null;

        if ($network->iaas_dhcp_server_id) {
            $dhcpServer = DhcpServers::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $network->iaas_dhcp_server_id)
                ->first();

            $dhcpServerIp = $dhcpServer?->ip_addr;
        }

        return [
            'name'             => $network->name,
            'ip_addr'          => $network->ip_addr,
            'ip_range_start'   => $network->ip_addr_range_start,
            'ip_range_end'     => $network->ip_addr_range_end,
            'gateway'          => $gatewayIp,
            'subnet'           => $subnet,
            'netmask'          => $netmask,
            'network'          => $networkAddr,
            'dhcp_server'      => $dhcpServerIp,
            'dns_nameservers'  => $network->dns_nameservers,
            'mtu'              => $network->mtu,
        ];
    }

    public static function getCloudInitNetworkConfiguration($vm) : string
    {
        $networkCards = VirtualMachinesService::getVirtualNetworkCards($vm);

        $networkCardsArray = [];

        foreach ($networkCards as $networkCard) {
            $networkCardsArray[$networkCard->name] = [
                'match' => [
                    'macaddress' => $networkCard->mac_addr
                ],
                'set-name' => $networkCard->name,
                'dhcp4' => true,
            ];

            $network = Networks::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $networkCard->iaas_network_id)
                ->first();

            $ips = IpAddresses::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_network_card_id', $networkCard->id)
                ->get();

            if($ips) {
                foreach ($ips as $ip) {
                    //  /32 fix
                    $subnet = explode('/', $network->cidr);

                    $ipAddr = $ip->ip_addr;

                    if (Str::contains($ipAddr, '/32')) {
                        $ipAddr = Str::replace('/32', '/' . $subnet[1], $ipAddr);
                    }

                    $gateway = $subnet[0];
                    $gateway = str_replace('.0', '.1', $gateway);

                    $networkCardsArray[$networkCard->name]['addresses'][] = $ipAddr;
                    $networkCardsArray[$networkCard->name]['gateway4'] = $gateway;

                    if($network->dns_nameservers) {
                        $nameServers = $network->dns_nameservers;

                        foreach ($nameServers as $nameServer) {
                            if(Str::contains($nameServer, '/32')) {
                                $nameServer = Str::replace('/32', '', $nameServer);
                            }

                            $networkCardsArray[$networkCard->name]['nameservers'] = [
                                'addresses' => $nameServer
                            ];
                        }
                    }

                    $networkCardsArray[$networkCard->name]['dhcp4'] = false;
                }
            }
        }

        $data = [
            'instance_id' => $vm->uuid,
            'hostname'  =>  $vm->hostname,
            'manage_etc_hosts' => true,
            'ssh_pwauth' => true,
            'disable_root' => false,
            'users' => [
                [
                    'name' => $vm->username,
                    'gecos' => 'Superuser',
                    'lock_passwd' => false,
                    'shell' => '/bin/bash',
                    'passwd' => $hash,
                ]
            ],
//            'network' => [
//                'version' => 2,
//                'ethernets' => $networkCardsArray
//            ],
        ];

        $yaml = yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);

        // Remove YAML document markers
        $yaml = preg_replace('/^---\s*\n/', '', $yaml);
        $yaml = preg_replace('/\n\.\.\.\s*$/', '', $yaml);

        // Prepend Cloud-Init header
        $yaml = "#cloud-config\n" . $yaml;

        return $yaml;
    }

    public static function getCloudInitConfiguration($vm) : string
    {
        //  For encryption of the password
        $salt = substr(str_replace('+', '.', base64_encode(random_bytes(16))), 0, 16);
        $hash = crypt(VirtualMachinesService::getRawPassword($vm), '$6$' . $salt . '$');

        $user = [
            'name'        => $vm->username,
            'gecos'       => 'Superuser',
            'lock_passwd' => false,
            'shell'       => '/bin/bash',
            'passwd'      => $hash,
        ];

        $sshKeys = self::collectSshKeys($vm);

        if (!empty($sshKeys)) {
            $user['ssh_authorized_keys'] = array_column($sshKeys, 'public_key');
        }

        $data = [
            'instance_id'      => $vm->uuid,
            'hostname'         => $vm->hostname,
            'manage_etc_hosts' => true,
            'ssh_pwauth'       => true,
            'disable_root'     => false,
            'users'            => [$user],
        ];

        $envVars = self::collectEnvVars($vm);
        $writeFiles = [];

        if (!empty($envVars)) {
            $envContent = '';
            foreach ($envVars as $key => $value) {
                $envContent .= strtoupper($key) . '=' . $value . "\n";
            }

            $writeFiles[] = [
                'path'        => '/etc/environment',
                'content'     => $envContent,
                'permissions' => '0644',
                'append'      => true,
            ];
        }

        $tokens = $vm->tokens ?? [];

        if (!empty($tokens)) {
            $writeFiles[] = [
                'path'        => '/etc/plusclouds/tokens.json',
                'content'     => json_encode($tokens, JSON_PRETTY_PRINT),
                'permissions' => '0600',
                'owner'       => 'root:root',
            ];
        }

        if (!empty($writeFiles)) {
            $data['write_files'] = $writeFiles;
        }

        $yaml = yaml_emit($data, YAML_UTF8_ENCODING, YAML_LN_BREAK);

        // Remove YAML document markers
        $yaml = preg_replace('/^---\s*\n/', '', $yaml);
        $yaml = preg_replace('/\n\.\.\.\s*$/', '', $yaml);

        // Prepend Cloud-Init header
        $yaml = "#cloud-config\n" . $yaml;

        return $yaml;
    }

    private static function collectSshKeys(VirtualMachines $vm): array
    {
        $sshKeys = [];

        $pivots = SshPublicKeyVirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();

        foreach ($pivots as $pivot) {
            $key = SshPublicKeys::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $pivot->iam_ssh_public_key_id)
                ->first();

            if ($key) {
                $sshKeys[] = [
                    'name'        => $key->name,
                    'public_key'  => $key->public_key,
                    'key_type'    => $key->key_type,
                    'fingerprint' => $key->fingerprint,
                ];
            }
        }

        return $sshKeys;
    }

    private static function collectEnvVars(VirtualMachines $vm): array
    {
        $envVars = [];

        // 1. Collect from EnvVarGroups linked to this VM, ordered by priority (lower = first, can be overridden)
        $vmEnvVarGroups = VirtualMachineEnvVarGroups::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->orderBy('priority')
            ->get();

        foreach ($vmEnvVarGroups as $vmGroup) {
            $groupVars = EnvVarGroupVars::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_env_var_group_id', $vmGroup->iaas_env_var_group_id)
                ->get();

            foreach ($groupVars as $var) {
                $envVars[$var->key] = $var->value;
            }
        }

        // 2. Direct VM env vars override group vars
        $directVars = VirtualMachineEnvVars::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();

        foreach ($directVars as $var) {
            $envVars[$var->key] = $var->value;
        }

        return $envVars;
    }

    /**
     * Reads the VM's selected service roles from features.service_roles and re-validates each one
     * against the iaas_ansible_roles catalog, dropping any role that has since been deactivated
     * (rather than failing metadata generation, since this runs on every config ISO rebuild).
     */
    private static function collectServiceRoles(VirtualMachines $vm): array
    {
        $serviceRoles = $vm->features['service_roles'] ?? [];

        if (!is_array($serviceRoles) || empty($serviceRoles)) {
            return [];
        }

        $roleNames = array_keys($serviceRoles);

        $activeRoleNames = AnsibleRoles::withoutGlobalScope(AuthorizationScope::class)
            ->whereIn('name', $roleNames)
            ->where('is_active', true)
            ->pluck('name')
            ->all();

        $collected = [];

        foreach ($serviceRoles as $name => $role) {
            if (!in_array($name, $activeRoleNames, true)) {
                Log::warning("[VirtualMachinesMetadataService@collectServiceRoles] Dropping service role [{$name}] for VM [{$vm->uuid}] - no longer an active iaas_ansible_roles entry.");
                continue;
            }

            $collected[$name] = $role;
        }

        return $collected;
    }
}
