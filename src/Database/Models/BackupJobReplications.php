<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\BackupJobReplicationsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * BackupJobReplications model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property integer $iaas_backup_job_id
 * @property integer $iaas_repository_id
 * @property string $replication_type
 * @property integer $iaas_backup_retention_policy_id
 * @property integer $priority
 * @property boolean $is_enabled
 * @property boolean $encrypt_in_transit
 * @property integer $bandwidth_limit_mbps
 * @property \Carbon\Carbon $last_replicated_at
 * @property string $last_replication_status
 * @property integer $last_replication_size_bytes
 * @property integer $last_replication_duration_secs
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class BackupJobReplications extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_backup_job_replications';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iaas_backup_job_id',
            'iaas_repository_id',
            'replication_type',
            'iaas_backup_retention_policy_id',
            'priority',
            'is_enabled',
            'encrypt_in_transit',
            'bandwidth_limit_mbps',
            'last_replicated_at',
            'last_replication_status',
            'last_replication_size_bytes',
            'last_replication_duration_secs',
            'iam_account_id',
            'iam_user_id',
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
    'iaas_backup_job_id' => 'integer',
    'iaas_repository_id' => 'integer',
    'replication_type' => 'string',
    'iaas_backup_retention_policy_id' => 'integer',
    'priority' => 'integer',
    'is_enabled' => 'boolean',
    'encrypt_in_transit' => 'boolean',
    'bandwidth_limit_mbps' => 'integer',
    'last_replicated_at' => 'datetime',
    'last_replication_status' => 'string',
    'last_replication_size_bytes' => 'integer',
    'last_replication_duration_secs' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_replicated_at',
    'created_at',
    'updated_at',
    'deleted_at',
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
        parent::observe(BackupJobReplicationsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_backup_job_replications');

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
