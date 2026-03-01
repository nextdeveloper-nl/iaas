<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\HealthChecksPerformanceObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * HealthChecksPerformance model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property string $object_type
 * @property integer $object_id
 * @property integer $total_checks
 * @property integer $healthy_count
 * @property integer $warning_count
 * @property integer $critical_count
 * @property integer $failed_count
 * @property \Carbon\Carbon $last_check_at
 * @property $avg_response_time_ms
 * @property string $overall_status
 */
class HealthChecksPerformance extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_health_checks_performance';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'object_type',
            'object_id',
            'total_checks',
            'healthy_count',
            'warning_count',
            'critical_count',
            'failed_count',
            'last_check_at',
            'avg_response_time_ms',
            'overall_status',
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
    'object_type' => 'string',
    'object_id' => 'integer',
    'total_checks' => 'integer',
    'healthy_count' => 'integer',
    'warning_count' => 'integer',
    'critical_count' => 'integer',
    'failed_count' => 'integer',
    'last_check_at' => 'datetime',
    'overall_status' => 'string',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_check_at',
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
        parent::observe(HealthChecksPerformanceObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_health_checks_performance');

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
