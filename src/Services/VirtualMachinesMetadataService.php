<?php

namespace NextDeveloper\IAAS\Services;

use App\Helpers\Http\ResponseHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Exceptions\NotFoundException;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\EnvVarGroupVars;
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
                'disk_type'     => $vdi->disk_type,
                'device_number' => $vdi->device_number,
                'total_disk'    => $vdi->size,
            ];
        }

        $vifConfiguration = [];

        foreach ($vifs as $vif) {
            $data = [
                'device_number' => $vif->device_number,
                'mac_addr'      => $vif->mac_addr,
//                'network'       => [
//                    'ip_addr'           => $vif->ip_addr,
//                    'ip_range_start'    => $vif->ip_range_start,
//                    'ip_range_end'      => $vif->ip_range_end,
//                    'gateway'           => $vif->gateway,
//                    'subnet'            => $vif->subnet,
//                    'netmask'           => $vif->netmask,
//                    'network'           => $vif->network,
//                    'dhcp_server'       => $vif->dhcp_server,
//                    'dns_nameservers'   => $vif->dns_nameservers,
//                    'mtu'               => $vif->mtu,
//                ],
            ];

            if($vif->ipList) {
                $data['ipList'] = [
                    'data' => $vif->ipList->map(function ($ip) {
                        return [
                            'id'            => $ip->id,
                            'ip_addr'      => $ip->ip_addr,
                            'version'      => $ip->version,
                            'is_reachable' => $ip->is_reachable
                        ];
                    }),
                ];
            }

            $vifConfiguration[] = $data;
        }

        $computePool = VirtualMachinesService::getComputePool($vm);

        $computePoolArray = [
            'id' => $computePool->uuid,
            'name' => $computePool->name,
            'pool_type' => $computePool->pool_type,
            'hypervisor_type' => $computePool->hypervisor_type,
            'hypervisor_version' => $computePool->hypervisor_version,
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
            'password' => $vm->password,
            'virtual_machine_id' => $vm->id_ref,
            'virtual_disks' => $diskConfiguration,
            'virtual_network_cards' => $vifConfiguration,
            'service_roles' => [
                //  Here will be roles of the server
                'zabbix_server' => [
                    'is_zabbix_enabled' => true,
                    'zabbix_server_ip'  => '185.255.172.221'
                ],
            ],
            'compute_pool' => $computePoolArray,
            'cloud_node' => $cloudPoolArray,
            'ssh_keys' => self::collectSshKeys($vm),
            'env_vars' => self::collectEnvVars($vm),
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
        $hash = crypt($vm->password, '$6$' . $salt . '$');

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

        if (!empty($envVars)) {
            $envContent = '';
            foreach ($envVars as $key => $value) {
                $envContent .= strtoupper($key) . '=' . $value . "\n";
            }

            $data['write_files'] = [
                [
                    'path'        => '/etc/environment',
                    'content'     => $envContent,
                    'permissions' => '0644',
                    'append'      => true,
                ]
            ];
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
}
