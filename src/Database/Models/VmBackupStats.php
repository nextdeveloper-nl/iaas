<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VmBackupStatsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VmBackupStats model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $vms_protected
 * @property integer $vms_protected_delta
 * @property $vms_protected_delta_pct
 * @property integer $rpo_breached_vms
 * @property integer $sla_breached_jobs
 * @property integer $jobs_disabled
 * @property integer $jobs_failed_24h
 * @property integer $jobs_failed_30d
 * @property $avg_rpo_achieved_hours
 * @property $avg_rpo_target_hours
 * @property integer $storage_used_bytes
 * @property $storage_used_gb
 * @property $storage_used_tb
 * @property integer $protections_done_24h
 * @property integer $protections_done_30d
 * @property integer $protections_done_delta
 * @property $protections_done_delta_pct
 * @property integer $jobs_with_replication
 */
class VmBackupStats extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_vm_backup_stats';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'vms_protected',
            'vms_protected_delta',
            'vms_protected_delta_pct',
            'rpo_breached_vms',
            'sla_breached_jobs',
            'jobs_disabled',
            'jobs_failed_24h',
            'jobs_failed_30d',
            'avg_rpo_achieved_hours',
            'avg_rpo_target_hours',
            'storage_used_bytes',
            'storage_used_gb',
            'storage_used_tb',
            'protections_done_24h',
            'protections_done_30d',
            'protections_done_delta',
            'protections_done_delta_pct',
            'jobs_with_replication',
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
    'vms_protected' => 'integer',
    'vms_protected_delta' => 'integer',
    'rpo_breached_vms' => 'integer',
    'sla_breached_jobs' => 'integer',
    'jobs_disabled' => 'integer',
    'jobs_failed_24h' => 'integer',
    'jobs_failed_30d' => 'integer',
    'storage_used_bytes' => 'integer',
    'protections_done_24h' => 'integer',
    'protections_done_30d' => 'integer',
    'protections_done_delta' => 'integer',
    'jobs_with_replication' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [

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
        parent::observe(VmBackupStatsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_vm_backup_stats');

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
