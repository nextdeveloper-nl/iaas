<?php
namespace NextDeveloper\IAAS\Actions\StoragePools;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\IAAS\Database\Models\Datacenters;
use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\IAAS\Database\Models\StorageVolumes;
use NextDeveloper\IAM\Database\Models\Users;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class Scan extends AbstractAction
{
    public const EVENTS = [
        'scanning:NextDeveloper\IAAS\StoragePools',
        'scanned:NextDeveloper\IAAS\StoragePools',
        'scan-failed:NextDeveloper\IAAS\StoragePools'
    ];

    public function __construct(StoragePools $pool, $params = null, $previous = null)
    {
        $this->model = $pool;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'Starting to scan storage pool: ' . $this->model->name);

        Log::info(__METHOD__ . ' | Scanning all storage volumes on pool: ' . $this->model->name);

        $volumes = StorageVolumes::withoutGlobalScope(AuthorizationScope::class)
            ->where('iaas_storage_pool_id', $this->model->id)
            ->get();

        foreach ($volumes as $volume) {
            Log::info(__METHOD__ . ' | Scanning storage volume: ' . $volume->name . '/' . $volume->uuid);
            dispatch(new \NextDeveloper\IAAS\Actions\StorageVolumes\Scan($volume));
        }

        $this->setFinished('Storage pool scanning is finished');
    }
}
