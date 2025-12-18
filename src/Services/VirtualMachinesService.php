<?php

namespace NextDeveloper\IAAS\Services;

use App\Helpers\Http\ResponseHelper;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\Commons\Exceptions\NotFoundException;
use NextDeveloper\Communication\Helpers\Communicate;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Actions\VirtualMachines\Commit;
use NextDeveloper\IAAS\Actions\VirtualMachines\Delete;
use NextDeveloper\IAAS\Actions\VirtualMachines\HealthCheck;
use NextDeveloper\IAAS\Database\Filters\VirtualMachinesQueryFilter;
use NextDeveloper\IAAS\Database\Models\CloudNodes;
use NextDeveloper\IAAS\Database\Models\ComputeMembers;
use NextDeveloper\IAAS\Database\Models\ComputePools;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualDiskImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineMetrics;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\IAAS\Database\Models\VirtualNetworkCards;
use NextDeveloper\IAAS\Exceptions\CannotCreateVirtualMachine;
use NextDeveloper\IAAS\Exceptions\CannotFindAvailableResourceException;
use NextDeveloper\IAAS\Exceptions\CannotUpdateResourcesException;
use NextDeveloper\IAAS\Helpers\IaasHelper;
use NextDeveloper\IAAS\Helpers\ResourceCalculationHelper;
use NextDeveloper\IAAS\ResourceLimiters\SimpleLimiter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\ComputeMemberXenService;
use NextDeveloper\IAAS\Services\Hypervisors\XenServer\VirtualMachinesXenService;
use NextDeveloper\IAM\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for VirtualMachines
 *
 * Class VirtualMachinesService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VirtualMachinesService extends AbstractVirtualMachinesService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
    public static function get(VirtualMachinesQueryFilter $filter = null, array $params = []): Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return parent::get($filter, $params);
    }

    public static function getOwnerAccount(VirtualMachines $vm) : ?Accounts
    {
        return UserHelper::getAccountById($vm->iam_account_id);
    }

    public static function getOwner(VirtualMachines $vm) : ?Users
    {
        return UserHelper::getUserWithId($vm->iam_user_id);
    }

    public static function getCdrom(VirtualMachines $vm) : ?VirtualDiskImages
    {
        return VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('is_cdrom', true)
            ->first();
    }

    public static function getAvailableMetrics(VirtualMachines $vm)
    {
        $metrics = VirtualMachineMetrics::withoutGlobalScopes()
            ->select('parameter')
            ->distinct()
            ->where('iaas_virtual_machine_id', $vm->id)
            ->pluck('parameter');

        return $metrics;
    }

    public static function getMetrics(VirtualMachines $vm, $metric)
    {
        if($metric == 'cpu')
            return self::getCpuMetrics($vm);

        if($metric == 'ram')
            return self::getRamMetrics($vm);

        $values = VirtualMachineMetrics::withoutGlobalScopes()
            ->select(['value', 'timestamp'])
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('parameter', $metric)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        if(Str::contains($metric, 'cpu')) {
            $values = $values->map(function ($item) {
                //  We are converting the CPU percentage to a number between 0 and 1
                $item->value = $item->value * 100;
                return $item;
            });
        }

        if($metric == 'memory_target' || $metric == 'memory') {
            $values = $values->map(function ($item) {
                //  We are converting the memory target to MB
                $item->value = $item->value / 1024 / 1024;
                return $item;
            });
        }

        if(Str::startsWith($metric, 'vif')) {
            $values = $values->map(function ($item) {
                //  We are converting the network traffic to kBits
                $item->value = $item->value / 1024;
                return $item;
            });
        }

        return $values->toArray();
    }

    public static function getRamMetrics(VirtualMachines $vm)
    {
        $values = VirtualMachineMetrics::withoutGlobalScopes()
            ->select(['parameter', 'value', 'timestamp'])
            ->where('iaas_virtual_machine_id', $vm->id)
            ->whereIn('parameter', ['memory', 'memory_target'])
            ->orderBy('created_at', 'desc')
            ->take(30) //  We are taking 30 values for each CPU
            ->get();

        $ramSeries = [];

        foreach ($values as $value) {
            $ramSeries[$value->parameter][] = [
                'timestamp' => $value->timestamp->timestamp,
                'value'     => $value->value * 100 //  We are converting the CPU percentage to a number between 0 and 1
            ];
        }

        return self::convertToApexChartData($ramSeries);
    }

    public static function getCpuMetrics(VirtualMachines $vm)
    {
        $cpuCount = $vm->cpu;
        $availableCpus = range(0, $cpuCount - 1);

        foreach ($availableCpus as &$cpu) {
            $cpu = 'cpu' . $cpu;
        }

        $values = VirtualMachineMetrics::withoutGlobalScopes()
            ->select(['parameter', 'value', 'timestamp'])
            ->where('iaas_virtual_machine_id', $vm->id)
            ->whereIn('parameter', $availableCpus)
            ->orderBy('created_at', 'desc')
            ->take($cpuCount*30) //  We are taking 30 values for each CPU
            ->get();

        $cpuSeries = [];

        foreach ($values as $value) {
            $cpuSeries[$value->parameter][] = [
                'timestamp' => $value->timestamp->timestamp,
                'value'     => $value->value * 100 //  We are converting the CPU percentage to a number between 0 and 1
            ];
        }

        return self::convertToApexChartData($cpuSeries);
    }

    /**
     *
     *
     * @param VirtualMachines $vm
     * @return RepositoryImages|null
     */
    public static function getRepositoryImage(VirtualMachines $vm) : ?RepositoryImages
    {
        return RepositoryImages::withoutGlobalScopes()->where('id', $vm->iaas_repository_image_id)->first();
    }

    public static function convertToApexChartData($rawData) {
        $result = [];
        $timezone = new DateTimeZone('Europe/Istanbul');

        foreach ($rawData as $cpu => $points) {
            $series = array_map(function ($p) use ($timezone) {
                $dt = new DateTime('@' . $p['timestamp']);
                $dt->setTimezone($timezone);

                return [
                    'x' => $dt->format('c'), // ISO 8601 in UTC
                    'y' => ceil($p['value'])
                ];
            }, array_reverse($points)); // Reverse to oldest-to-newest

            $result[] = [
                'name' => $cpu,
                'data' => $series
            ];
        }

        return $result;
    }


    public static function getVirtualMachineByHypervisorUuid($uuid) : ?VirtualMachines
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('hypervisor_uuid', $uuid)
            ->first();

        return $vm;
    }

    public static function create(array $data)
    {
        //  Here we check if we are hitting the limits
        $hasLimits = (new SimpleLimiter(IaasHelper::currentAccount()))->hasLimitForRam($data['ram']);

        if(!$hasLimits) {
            throw new CannotCreateVirtualMachine('You reached to limits of your account. You cannot have more ram in your account. Please consult to sales teams.');
        }

        //  Getting the actual amount of ram
        $data['ram']    =   ResourceCalculationHelper::getActualRam($data['ram']);

        //  Asking the appropriate number of CPU per ram.
        $data['cpu']    =   ResourceCalculationHelper::getCpuPerRam(
            ram: $data['ram'],
            //  We will be adding this parameter later to get the actual CPU size for compute pool
            cp: null
        );

        //  Finding and attaching cloud node id
        if(array_key_exists('iaas_compute_pool_id', $data)) {
            $computePool = null;

            if(Str::isUuid($data['iaas_compute_pool_id'])) {
                $computePool = ComputePools::where('uuid', $data['iaas_compute_pool_id'])->first();
            } else {
                $computePool = ComputePools::where('id', $data['iaas_compute_pool_id'])->first();
            }

            $cloudNode = CloudNodes::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $computePool->iaas_cloud_node_id)
                ->first();

            $data['iaas_cloud_node_id'] = $cloudNode->id;
        }

        //  So with this setup, we set our maximum available ram to 2048 GB
        $data['ram'] = ResourceCalculationHelper::getRamInMb($data['ram']);

        return parent::create($data);
    }

    /**
     * @param \NextDeveloper\IAM\Database\Models\Accounts $account
     * @return Collection
     */
    public static function getVirtualMachines(Accounts $account) : Collection
    {
        return VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iam_account_id', $account->id)
            ->get();
    }

    public static function getVirtualDiskImages(VirtualMachines $vm) : Collection
    {
        return VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();
    }

    public static function getVirtualNetworkCards(VirtualMachines $vm) : Collection
    {
        return VirtualNetworkCards::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->get();
    }

    public static function getComputeMember(VirtualMachines $vm) : ?ComputeMembers
    {
        return ComputeMembers::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_member_id)
            ->first();
    }

    public static function getComputePool(VirtualMachines $vm) : ?ComputePools
    {
        return ComputePools::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $vm->iaas_compute_pool_id)
            ->first();
    }

    public static function getCloudPool($vm) {
        $computePool = self::getComputePool($vm);

        return CloudNodes::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $computePool->iaas_cloud_node_id)
            ->first();
    }

    public static function getRawPasswordById($id)
    {
        $vm = null;

        if(Str::isUuid($id))
            $vm = VirtualMachines::where('uuid', $id)->first();
        else
            $vm = VirtualMachines::where('id', $id)->first();

        try {
            $password = decrypt($vm->password);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            if($e->getMessage() == 'The payload is invalid.') {
                Log::error(__METHOD__ . ' | We got the payload is invalid error. Maybe the password is not ' .
                    'encrpyted for the customer. That is why I am returning the raw password');

                $vm->update([
                    'password'  =>  $vm->password
                ]);

                return $vm->password;
            }
        }

        return $password;
    }

    public static function getPasswordById($id)
    {
        return ResponseHelper::status(
            self::getRawPasswordById($id)
        );
    }

    public static function update($id, array $data)
    {
        $vm = VirtualMachines::findByUuid($id);
        $cp = self::getComputePool($vm);

        $triggerVdiUpdate = false;
        $triggerRamUpdate = false;

        if(!$vm) {
            throw new NotFoundException('Cannot find the virtual machine you are trying to update. This ' .
                'can be because of multiple reasons but most probably vm is not there. Therefore it can be a wise ' .
                'decision to run a manual health check for this VM.');
        }

        //  Sometimes ram can be null and we want to change something else with the virtual machinne
        //  like backup routine
        if(array_key_exists('ram', $data)) {
            if($vm->ram != $data['ram']) {
                if($vm->hypervisor_uuid) {
                    if($vm->status != 'halted')
                        throw new CannotUpdateResourcesException('Unfortunately we cannot update the resources ' .
                            'of your virtual machine because your virtual machine is still running. Please shutdown your ' .
                            'server and try updating the resources again.');
                }

                $canUpdateRam = self::canUpdateRam($vm, $data['ram']);

                if(!$canUpdateRam) {
                    $availableRamSizes = ResourceCalculationHelper::getAvailableRamSizes($cp);

                    throw new CannotUpdateResourcesException('We cannot update the ram and cpu because the ram ' .
                        'that you are asking to increase is either beyond our available ram or the amount of ram is not ' .
                        'in the list of available ram amounts. To fix this problem please check if the ram size is ' .
                        'within this list: ' . implode(' GB, ', $availableRamSizes) . ' GB');
                }

                /*  If we can update the ram, we should also take a look at the disk. Because if the server is in STAR
                *   design we can update but if we are in ONE design we should check if we can update the disk also
                */
                $shouldUpdateDisk = self::shouldUpdateDiskWithRam($vm);

                //  If we should update then I am updating the disk
                //  Also if we should update the disk this means that the pool is ONE
                if($shouldUpdateDisk) {
                    //  Since this is Leo One type or pool, we cannot allow to reduce resources.
                    if(ResourceCalculationHelper::getRamInMb($data['ram']) < $vm->ram) {
                        throw new CannotUpdateResourcesException('We cannot update resources of this server,' .
                            ' because the server is in Leo ONE pool where cpu, ram and disk resources are aligned with a ' .
                            'certain ratio. The problem here is that we cannot reduce the size of the disk, therefore ' .
                            'we cannot reduce the size of CPU and RAM. We are very sorry about this issue.');
                    }

                    //  @leo-pool ONE
                    //  If we came to this point this means that we have enough resources in the resource pool.
                    $cm = self::getComputeMember($vm);

                    //  If we have a compute member, this means that we should be taking a look at the CM resources.
                    //  If CM also has resource then everything is fine, we can move on.
                    if($cm) {
                        //  Since the ram and disk are correlated in this design, we don't need to check for disk again.
                        if(!ComputeMembersService::hasRamResource($cm, $data['ram'])) {
                            throw new CannotUpdateResourcesException('We cannot update your virtual machines ' .
                                'resources because on the host that you are using there is not enough resource. You ' .
                                'should create a new server or you should enable migrate server option while asking for ' .
                                'resize. But you should be aware that when you are migrating your server, you will have ' .
                                'some downtime. Also you may not have the same hardware and your bios may change.');
                        }
                    }

                    //  This means that we have done all the checks and we are good to go for VDI update
                    $triggerVdiUpdate = true;
                }

                if(!$shouldUpdateDisk) {
                    //  @leo-pool STAR
                    $canUpdateDisk = self::canUpdateDisk(
                        vm: $vm,
                        toDisk: ResourceCalculationHelper::getDiskSizeAgainstRam(
                            cp: self::getComputePool($vm),
                            ram: $data['ram']
                        )
                    );

                    $availableDiskSizes = ResourceCalculationHelper::getAvailableDiskSizes(
                        cp: self::getComputePool($vm),
                        minSize: ResourceCalculationHelper::getDiskSizeAgainstRam(
                            cp: self::getComputePool($vm),
                            ram: $data['ram']
                        )
                    );

                    if(!$canUpdateDisk) {
                        throw new CannotUpdateResourcesException('We cannot update the disk. The disk you are ' .
                            'requesting either is not available or you cannot take that much. Try to ask for these ' .
                            'amounts; ' . implode(' GB, ', $availableDiskSizes) . ' GB. Or you may have requested ' .
                            'either ram to change or disk to change. If the compute pool is in one mode and you asked for ' .
                            'ram to change, then we should also change the disk.');
                    }
                }

                $triggerRamUpdate = true;
            }
        }

        if($triggerVdiUpdate) {
            $vdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_virtual_machine_id', $vm->id)
                ->where('device_number', 0)
                ->first();

            VirtualDiskImagesService::update($vdi->id, [
                'size'  =>  ResourceCalculationHelper::getDiskSizeAgainstRam($cp, $data['ram'])
            ]);
        } else {
            $data['status'] = 'pending-update';
        }

        if(!$triggerRamUpdate) {
            unset($data['cpu']);
            unset($data['ram']);
        } else {
            $data['cpu']    = ResourceCalculationHelper::getCpuPerRam($data['ram'], $cp);
            $data['ram']    = ResourceCalculationHelper::getRamInMb($data['ram']);
            $data['status'] = 'pending-update';
        }

        if(array_key_exists('backup_repository_id', $data)) {
            $repository = Repositories::withoutGlobalScope(AuthorizationScope::class)
                ->where('uuid', $data['backup_repository_id'])
                ->first();

            if($repository) {
                $data['backup_repository_id'] = $repository->id;
            }
        }

        $updatedVm = parent::update($id, $data);

        if($vm->hypervisor_uuid) {
            dispatch(new Commit($vm));
        }

        return $updatedVm;
    }

    /**
     * If the compute pool design is in One design then we should update the disk, if it is in star design, then we
     * dont need to update the disk.
     *
     * @param VirtualMachines $vm
     * @return bool
     */
    public static function shouldUpdateDiskWithRam(VirtualMachines $vm)
    {
        $cp = self::getComputePool($vm);

        return $cp->pool_type == 'one';
    }

    public static function getPoolType(VirtualMachines $vm)
    {
        $cp = self::getComputePool($vm);

        return $cp->pool_type;
    }

    public static function canUpdateDisk(VirtualMachines $vm, $toDisk) {
        //  At the moment we are not letting the customer make live resource update. That is why we are checking if
        //  the VM is shutdown or not.
        if(!($vm->status == 'draft' || $vm->status == 'halted'))
            return false;

        if($vm->iaas_compute_member_id) {
            $availableDiskSizes = ResourceCalculationHelper::getAvailableDiskSizesForComputeMember(
                cm: self::getComputeMember($vm)
            );

            return $availableDiskSizes;
        }

        return ResourceCalculationHelper::getAvailableDiskSizes(
            cp: self::getComputePool($vm)
        );
    }

    /**
     * Here we are checking if we can update the amount of ram to the given ram, according to the vm resource
     * configuration given by the administrator of this system.
     *
     * @param VirtualMachines $vm
     * @param $toRam
     * @return void
     */
    public static function canUpdateRam(VirtualMachines $vm, $toRam) {
        //  At the moment we are not letting the customer make live resource update. That is why we are checking if
        //  the VM is shutdown or not.
        if(!($vm->status == 'draft' || $vm->status == 'halted'))
            return false;

        //  This means that we need to check the ram because the user requested another ram
        $availableRamSizes = ResourceCalculationHelper::getAvailableRamSizes(
            cp: self::getComputePool($vm)
        );

        if(!in_array($toRam, $availableRamSizes)) {
            return false;
        }

        return true;
    }

    public static function isRunning(VirtualMachines $vm, $force = false) : bool
    {
        if($force) {
            (new HealthCheck($vm))->handle();
        }

        return $vm->status == 'running';
    }

    public static function getConsoleDataWithPerspective(VirtualMachinesPerspective $vm)
    {
        return self::getConsoleData(
            VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $vm->id)
                ->first()
        );
    }

    public static function getConsoleDataFromVmId($id) : array
    {
        $vm = VirtualMachines::where('uuid', $id)->first();

        return self::getConsoleData($vm);
    }

    public static function getConsoleData(VirtualMachines $vm) : array
    {
        if($vm->status == 'halted') {
            return [
                'console'   =>  'Not available while the server is shutdown.'
            ];
        }

        $key = config('iaas.console.key');
        $iv = config('iaas.console.iv');
        $t = time();

        $encrypt = function ($string) use ($key, $iv) {
            $method = 'AES-256-CBC';
            $output = openssl_encrypt($string, $method, $key, true, $iv);
            return base64_encode($output);
        };

        if($vm->console_data == null) {
            //  If we dont have the console data we are updating the VM
            (new HealthCheck($vm))->handle();
        }

        $vm = $vm->fresh();
        $computeMember = self::getComputeMember($vm);

        if(!$vm)
            return [];

        if(!$vm->console_data)
            return [];

        if($vm->status == 'draft' || $vm->status == 'halted')
            return [];

        if(!array_key_exists('uuid', $vm->console_data)) {
            dispatch(new HealthCheck($vm));
            return [];
        }

        $uuid = $vm->console_data['uuid'];
        $ipAddr = $computeMember->ip_addr;

        if($computeMember->is_behind_firewall)
            $ipAddr = $computeMember->local_ip_addr;

        if(Str::contains($ipAddr, '/'))
            $ipAddr = explode('/', $ipAddr)[0];

        $password = $computeMember->ssh_username . ':' . decrypt($computeMember->ssh_password);
        $endpoint = $ipAddr . '/console?uuid=' . $uuid;

        Log::info(__METHOD__ . ' | Endpoint: ' . $endpoint);

        $data = [
            $endpoint,
            base64_encode($password),
        ];

        return [
            'data' => $encrypt(implode('|', $data)),
            't'    => $t,
            'sign'  =>  md5($key.$t.$endpoint.$key)
        ];
    }

    public static function fixUsername(VirtualMachines $vm)
    {
        Log::info('[VirtualMachineService@fixUsername] Will try to fix the username. Current username: ' . $repoImage->default_username ?? 'root');

        if($vm->username)
            return $vm;

        Log::info('[VirtualMachineService@fixUsername] VM Data: ' . print_r($vm, true));

        switch ($vm->os) {
            case 'microsoft windows':
                $vm->update(['username' => 'Administrator']);
                break;
            case 'linux':
            case 'application':
                $repoImage = RepositoryImages::withoutGlobalScope(AuthorizationScope::class)->where('id', $vm->iaas_repository_image_id)->first();
                Log::info('[VirtualMachineService@fixUsername] Fixing the username as: ' . $repoImage->default_username ?? 'root');
                $vm->update(['username' => $repoImage->default_username ?? 'root']);
                break;
            default:
                dd('stop');
                $repoImage = RepositoryImages::withoutGlobalScope(AuthorizationScope::class)->where('id', $vm->iaas_repository_image_id)->first();
                $vm->update(['username' => $repoImage->default_username ?? 'root']);
        }

        return $vm->fresh();
    }

    public static function fixHostname(VirtualMachines $vm)
    {
        if(!$vm->hostname) {
            $vm->update([
                'hostname' => Str::kebab($vm->name)
            ]);
        }

        return $vm->fresh();
    }

    public static function delete($id)
    {
        $vm = VirtualMachines::findByUuid($id);
        dispatch(new Delete($vm));
    }

    public static function getPerformanceSnapshot($vm)
    {
        $cpuCount = $vm->cpu;

        $cpus = range(0, $cpuCount - 1);
        foreach ($cpus as &$cpu) {
            $cpu = 'cpu' . $cpu;
        }

        $cpuLoad = VirtualMachineMetrics::withoutGlobalScopes()
            ->select(['parameter', 'value', 'timestamp'])
            ->where('iaas_virtual_machine_id', $vm->id)
            ->whereIn('parameter', $cpus)
            ->orderBy('created_at', 'desc')
            ->take($cpuCount * 1) //  We are taking 60 values for each CPU
            ->get()
            ->toArray();

        $averageCpu = 0;
        $averageRam = 0;

        if(count($cpuLoad)) {
            $averageCpu = array_sum(array_column($cpuLoad, 'value')) / count($cpuLoad) * 100; //  Convert to percentage

            $ramLoad = VirtualMachineMetrics::withoutGlobalScopes()
                ->select(['parameter', 'value', 'timestamp'])
                ->where('iaas_virtual_machine_id', $vm->id)
                ->where('parameter', 'memory')
                ->orderBy('created_at', 'desc')
                ->take(1) //  We are taking the latest value
                ->get()
                ->toArray();

            $averageRam = $ramLoad[0]['value'] / 1024 / 1024; // Convert to MB
        }

        $vdi = VirtualDiskImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_virtual_machine_id', $vm->id)
            ->where('device_number', 0)
            ->first();

        return [
            'average_cpu' => ceil($averageCpu),
            'average_ram' => ceil($averageRam),
            'disk_utilisation' => $vdi ? ceil($vdi->physical_utilisation / $vdi->size * 100) : 100,
        ];
    }

    public static function getMetadata(VirtualMachines $vm = null) : array
    {
        if(!$vm) {
            return [
                'error' =>  'Virtual machine not found. Please provide a valid virtual machine instance.'
            ];
        }

        return VirtualMachinesMetadataService::getMetadata($vm);
    }

    public static function getCloudInitConfiguration($vm)
    {
        return VirtualMachinesMetadataService::getCloudInitConfiguration($vm);
    }

    public static function finalizeCommit(string $vm)
    {
        $vm = VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $vm)
            ->first();

        UserHelper::setUserById($vm->iam_user_id);
        UserHelper::setCurrentAccountById($vm->iam_account_id);

        $vm = self::fixHypervisorUuid($vm);

        dispatch(new Commit($vm));

        return $vm;
    }

    public static function fixHypervisorUuid(VirtualMachines $vm) : VirtualMachines
    {
        $computeMember = VirtualMachinesService::getComputeMember($vm);

        $uuid = ComputeMemberXenService::getVirtualMachineUuidByName($computeMember, $vm->uuid);

        UserHelper::runAsAdmin(function () use ($uuid, $vm) {
            $vm->update([
                'hypervisor_uuid' => $uuid
            ]);
        });

        return $vm->fresh();
    }
}
