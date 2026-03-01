<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use NextDeveloper\IAAS\Database\Filters\VmBackupHeatmapStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\VmBackupHeatmapStats;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVmBackupHeatmapStatsService;

/**
 * This class is responsible from managing the data for VmBackupHeatmapStats
 *
 * Class VmBackupHeatmapStatsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class VmBackupHeatmapStatsService extends AbstractVmBackupHeatmapStatsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function get(VmBackupHeatmapStatsQueryFilter $filter = null, array $params = []): Collection|LengthAwarePaginator
    {
        return VmBackupHeatmapStats::orderBy('backup_date', 'asc')->get();
    }
}
