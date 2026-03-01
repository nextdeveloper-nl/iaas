<?php

namespace NextDeveloper\IAAS\Services;

use NextDeveloper\IAAS\Database\Filters\VmBackupHeatmapByCloudStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmapByCloudStats;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVmBackupHeatmapByCloudStatsService;

/**
 * This class is responsible from managing the data for VmBackupHeatmapByCloudStats
 *
 * Class VmBackupHeatmapByCloudStatsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VmBackupHeatmapByCloudStatsService extends AbstractVmBackupHeatmapByCloudStatsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function get(VmBackupHeatmapByCloudStatsQueryFilter $filter = null, array $params = []): \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return VmBackupHeatmapByCloudStats::orderBy('backup_date', 'asc')->get();
    }
}
