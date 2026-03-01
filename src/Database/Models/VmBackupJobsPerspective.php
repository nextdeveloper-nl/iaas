<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VmBackupJobsPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VmBackupJobsPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $job_name
 * @property string $job_type
 * @property integer $iam_account_id
 * @property integer $iaas_virtual_machine_id
 * @property boolean $is_enabled
 * @property $expected_rpo_hours
 * @property $expected_rto_hours
 * @property integer $max_allowed_failures
 * @property $sla_target_pct
 * @property string $notification_webhook
 * @property array $email_notification_recipients
 * @property string $virtual_machine_name
 * @property string $hostname
 * @property string $retention_policy_name
 * @property integer $keep_for_days
 * @property integer $keep_last_n_backups
 * @property boolean $is_scheduled
 * @property \Carbon\Carbon $last_run_at
 * @property \Carbon\Carbon $last_run_ended_at
 * @property string $last_run_status
 * @property integer $last_run_progress
 * @property integer $last_run_duration_secs
 * @property integer $last_run_size_bytes
 * @property integer $consecutive_failures
 * @property boolean $rpo_breached
 * @property integer $rpo_achieved_hours
 * @property boolean $sla_breached
 * @property string $status_indicator
 * @property integer $replication_count
 * @property integer $replication_ok_count
 * @property integer $replication_failed_count
 * @property \Carbon\Carbon $last_replication_at
 * @property string $replication_status_indicator
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class VmBackupJobsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = true;

    protected $table = 'iaas_vm_backup_jobs_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'job_name',
            'job_type',
            'iam_account_id',
            'iaas_virtual_machine_id',
            'is_enabled',
            'expected_rpo_hours',
            'expected_rto_hours',
            'max_allowed_failures',
            'sla_target_pct',
            'notification_webhook',
            'email_notification_recipients',
            'virtual_machine_name',
            'hostname',
            'retention_policy_name',
            'keep_for_days',
            'keep_last_n_backups',
            'is_scheduled',
            'last_run_at',
            'last_run_ended_at',
            'last_run_status',
            'last_run_progress',
            'last_run_duration_secs',
            'last_run_size_bytes',
            'consecutive_failures',
            'rpo_breached',
            'rpo_achieved_hours',
            'sla_breached',
            'status_indicator',
            'replication_count',
            'replication_ok_count',
            'replication_failed_count',
            'last_replication_at',
            'replication_status_indicator',
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
    'id' => 'integer',
    'job_name' => 'string',
    'job_type' => 'string',
    'iaas_virtual_machine_id' => 'integer',
    'is_enabled' => 'boolean',
    'expected_rpo_hours' => 'double',
    'expected_rto_hours' => 'double',
    'max_allowed_failures' => 'integer',
    'sla_target_pct' => 'double',
    'notification_webhook' => 'string',
    'email_notification_recipients' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'virtual_machine_name' => 'string',
    'hostname' => 'string',
    'retention_policy_name' => 'string',
    'keep_for_days' => 'integer',
    'keep_last_n_backups' => 'integer',
    'is_scheduled' => 'boolean',
    'last_run_at' => 'datetime',
    'last_run_ended_at' => 'datetime',
    'last_run_status' => 'string',
    'last_run_progress' => 'integer',
    'last_run_duration_secs' => 'integer',
    'last_run_size_bytes' => 'integer',
    'consecutive_failures' => 'integer',
    'rpo_breached' => 'boolean',
    'rpo_achieved_hours' => 'integer',
    'sla_breached' => 'boolean',
    'status_indicator' => 'string',
    'replication_count' => 'integer',
    'replication_ok_count' => 'integer',
    'replication_failed_count' => 'integer',
    'last_replication_at' => 'datetime',
    'replication_status_indicator' => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_run_at',
    'last_run_ended_at',
    'last_replication_at',
    'created_at',
    'updated_at',
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
        parent::observe(VmBackupJobsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_vm_backup_jobs_perspective');

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
