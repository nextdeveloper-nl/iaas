<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Actions\VirtualMachines\Backup;
use NextDeveloper\IAAS\Database\Filters\BackupJobsQueryFilter;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\IAAS\Jobs\BackupJobs\CreateDefaultBackupJobsJob;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractBackupJobsService;

/**
 * This class is responsible from managing the data for BackupJobs
 *
 * Class BackupJobsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class BackupJobsService extends AbstractBackupJobsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function get(BackupJobsQueryFilter $filter = null, array $params = []): \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        if(array_key_exists('objectType', $params)) {
            if(!array_key_exists('objectId', $params)) {
                throw new \Exception("When you are filtering by object_type, you must provide also object_id.");
            }

            $objectType = request()->get('objectType');
            $objectType = explode('\\', $objectType);
            $objectType = $objectType[0] . '\\' . $objectType[1] . '\\Database\\Models\\' . $objectType[2];

            $objectId = request()->get('objectId');
            $object = app($objectType)->where('uuid', $objectId)->first();

            $list = BackupJobs::where('object_type', '=', $objectType)
                ->where('object_id', '=', $object->id)
                ->get();

            if($list->count() == 0) {
                switch($objectType) {
                    case \NextDeveloper\IAAS\Database\Models\VirtualMachines::class:
                        self::createDefaultVmBackupJob($object);
                        break;
                    default:
                        throw new \Exception("The object type is not supported for creating default backup jobs: " . $objectType);
                }

                $list = BackupJobs::where('object_type', '=', $objectType)
                    ->where('object_id', '=', $object->id)
                    ->get();
            }

            return $list;
        }

        return parent::get($filter, $params);
    }

    public static function createDefaultVmBackupJob(VirtualMachines $vm) : BackupJobs
    {
        $cloudPool = VirtualMachinesService::getCloudPool($vm);
        $backupRetentionPolicy = BackupRetentionPoliciesService::getDefault();

        $object = get_class($vm);

        $defaultBackupJob = \NextDeveloper\IAAS\Database\Models\BackupJobs::where('object_type', $object)
            ->where('object_id', $vm->id)
            ->first();

        if(!$defaultBackupJob) {
            $data = [
                'object_type' => $object,
                'object_id'   => $vm->id,
                'iaas_backup_retention_policy_id' => $backupRetentionPolicy->id,
                'iaas_repository_id' => $cloudPool->backup_repository_id,
                'name' => 'Default backup job',
                'description' => 'This is the default backup job',
                'iam_account_id'    =>  $vm->iam_account_id,
                'iam_user_id'   =>  $vm->iam_user_id
            ];

            $defaultBackupJob = BackupJobs::create($data);
        }

        return $defaultBackupJob;
    }
}
