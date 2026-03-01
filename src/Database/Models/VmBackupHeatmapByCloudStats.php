<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VmBackupHeatmapByCloudStatsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VmBackupHeatmapByCloudStats model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $iaas_cloud_node_id
 * @property string $cloud_node_name
 * @property integer $iaas_datacenter_id
 * @property string $datacenter_name
 * @property \Carbon\Carbon $backup_date
 * @property integer $day_offset
 * @property string $day_of_week
 * @property integer $distinct_jobs
 * @property integer $rpo_breach_count
 * @property string $day_status
 * @property integer $total_runs
 * @property integer $success_runs
 * @property integer $failed_runs
 * @property integer $day_size_bytes
 * @property integer $avg_duration_secs
 */
class VmBackupHeatmapByCloudStats extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_vm_backup_heatmap_by_cloud_stats';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iaas_cloud_node_id',
            'cloud_node_name',
            'iaas_datacenter_id',
            'datacenter_name',
            'backup_date',
            'day_offset',
            'day_of_week',
            'distinct_jobs',
            'rpo_breach_count',
            'day_status',
            'total_runs',
            'success_runs',
            'failed_runs',
            'day_size_bytes',
            'avg_duration_secs',
    ];

    /**
      Here we have the fulltext fields. We can use these for fulltext search if enabled.
     */
    protected $fullTextFields = [

    ];

    /**
     @var array
     */
    protected $appends = [

    ];

    /**
     We are casting fields to objects so that we can work on them better
     *
     @var array
     */
    protected $casts = [
    'iaas_cloud_node_id' => 'integer',
    'cloud_node_name' => 'string',
    'iaas_datacenter_id' => 'integer',
    'datacenter_name' => 'string',
    'backup_date' => 'datetime',
    'day_offset' => 'integer',
    'day_of_week' => 'string',
    'distinct_jobs' => 'integer',
    'rpo_breach_count' => 'integer',
    'day_status' => 'string',
    'total_runs' => 'integer',
    'success_runs' => 'integer',
    'failed_runs' => 'integer',
    'day_size_bytes' => 'integer',
    'avg_duration_secs' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'backup_date',
    ];

    /**
     @var array
     */
    protected $with = [

    ];

    /**
     @var int
     */
    protected $perPage = 20;

    /**
     @return void
     */
    public static function boot()
    {
        parent::boot();

        //  We create and add Observer even if we wont use it.
        parent::observe(VmBackupHeatmapByCloudStatsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_vm_backup_heatmap_by_cloud_stats');

        if(!$modelScopes) { $modelScopes = [];
        }
        if (!$globalScopes) { $globalScopes = [];
        }

        $scopes = array_merge(
            $globalScopes,
            $modelScopes
        );

        if($scopes) {
            foreach ($scopes as $scope) {
                static::addGlobalScope(app($scope));
            }
        }
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
