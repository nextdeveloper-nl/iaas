<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\ActiveAlarmsPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * ActiveAlarmsPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $object_type
 * @property integer $object_id
 * @property string $check_type
 * @property string $check_status
 * @property string $severity
 * @property string $error_message
 * @property string $error_code
 * @property integer $response_time_ms
 * @property \Carbon\Carbon $checked_at
 * @property \Carbon\Carbon $next_check_at
 * @property $check_data
 * @property $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ActiveAlarmsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = true;

    protected $table = 'iaas_active_alarms_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'object_type',
            'object_id',
            'check_type',
            'check_status',
            'severity',
            'error_message',
            'error_code',
            'response_time_ms',
            'checked_at',
            'next_check_at',
            'check_data',
            'metadata',
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
    'object_type' => 'string',
    'object_id' => 'integer',
    'check_type' => 'string',
    'check_status' => 'string',
    'severity' => 'string',
    'error_message' => 'string',
    'error_code' => 'string',
    'response_time_ms' => 'integer',
    'checked_at' => 'datetime',
    'next_check_at' => 'datetime',
    'check_data' => 'array',
    'metadata' => 'array',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'checked_at',
    'next_check_at',
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
        parent::observe(ActiveAlarmsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_active_alarms_perspective');

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
