<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VirtualMachineMigrationsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VirtualMachineMigrations model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property integer $iaas_virtual_machine_id
 * @property integer $source_iaas_compute_member_id
 * @property integer $target_iaas_compute_member_id
 * @property integer $source_iaas_storage_volume_id
 * @property integer $target_iaas_storage_volume_id
 * @property integer $source_iaas_storage_member_id
 * @property integer $target_iaas_storage_member_id
 * @property $status
 * @property $current_step
 * @property integer $progress
 * @property string $step_message
 * @property string $error_message
 * @property $options
 * @property \Carbon\Carbon $started_at
 * @property \Carbon\Carbon $completed_at
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class VirtualMachineMigrations extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_virtual_machine_migrations';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iaas_virtual_machine_id',
            'source_iaas_compute_member_id',
            'target_iaas_compute_member_id',
            'source_iaas_storage_volume_id',
            'target_iaas_storage_volume_id',
            'source_iaas_storage_member_id',
            'target_iaas_storage_member_id',
            'status',
            'current_step',
            'progress',
            'step_message',
            'error_message',
            'options',
            'started_at',
            'completed_at',
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
    'iaas_virtual_machine_id' => 'integer',
    'source_iaas_compute_member_id' => 'integer',
    'target_iaas_compute_member_id' => 'integer',
    'source_iaas_storage_volume_id' => 'integer',
    'target_iaas_storage_volume_id' => 'integer',
    'source_iaas_storage_member_id' => 'integer',
    'target_iaas_storage_member_id' => 'integer',
    'progress' => 'integer',
    'step_message' => 'string',
    'error_message' => 'string',
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
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
    'started_at',
    'completed_at',
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
        parent::observe(VirtualMachineMigrationsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_machine_migrations');

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
