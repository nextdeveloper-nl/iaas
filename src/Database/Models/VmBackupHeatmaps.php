<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VmBackupHeatmapsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VmBackupHeatmaps model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $iaas_backup_job_id
 * @property string $job_name
 * @property string $job_type
 * @property integer $iam_account_id
 * @property boolean $is_enabled
 * @property $expected_rpo_hours
 * @property string $virtual_machine_name
 * @property string $hostname
 * @property \Carbon\Carbon $backup_date
 * @property integer $day_offset
 * @property string $day_of_week
 * @property string $day_status
 * @property boolean $is_rpo_breach
 * @property integer $total_runs
 * @property integer $success_runs
 * @property integer $failed_runs
 * @property integer $day_size_bytes
 * @property integer $avg_duration_secs
 */
class VmBackupHeatmaps extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_vm_backup_heatmap';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iaas_backup_job_id',
            'job_name',
            'job_type',
            'iam_account_id',
            'is_enabled',
            'expected_rpo_hours',
            'virtual_machine_name',
            'hostname',
            'backup_date',
            'day_offset',
            'day_of_week',
            'day_status',
            'is_rpo_breach',
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
    'iaas_backup_job_id' => 'integer',
    'job_name' => 'string',
    'job_type' => 'string',
    'is_enabled' => 'boolean',
    'expected_rpo_hours' => 'double',
    'virtual_machine_name' => 'string',
    'hostname' => 'string',
    'backup_date' => 'datetime',
    'day_offset' => 'integer',
    'day_of_week' => 'string',
    'day_status' => 'string',
    'is_rpo_breach' => 'boolean',
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
        parent::observe(VmBackupHeatmapsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_vm_backup_heatmap');

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
