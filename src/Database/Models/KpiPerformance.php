<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\KpiPerformanceObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * KpiPerformance model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $active_clouds
 * @property integer $active_clouds_delta
 * @property $active_clouds_delta_pct
 * @property integer $compute_vcpus
 * @property integer $compute_vcpus_delta
 * @property $compute_vcpus_delta_pct
 * @property $storage_pb
 * @property $storage_pb_delta
 * @property $storage_pb_delta_pct
 * @property integer $active_tenants
 * @property integer $active_tenants_delta
 * @property $active_tenants_delta_pct
 * @property integer $alarm_count
 * @property integer $alarm_count_delta
 * @property $alarm_count_delta_pct
 * @property integer $alarm_critical_count
 * @property integer $alarm_high_count
 * @property integer $alarm_low_count
 * @property integer $alarm_compute_members_count
 * @property integer $alarm_storage_members_count
 * @property integer $alarm_network_members_count
 * @property integer $alarm_virtual_machines_count
 * @property $bandwidth_gbps
 * @property $bandwidth_gbps_delta
 * @property $bandwidth_gbps_delta_pct
 */
class KpiPerformance extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_kpi_performance';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'active_clouds',
            'active_clouds_delta',
            'active_clouds_delta_pct',
            'compute_vcpus',
            'compute_vcpus_delta',
            'compute_vcpus_delta_pct',
            'storage_pb',
            'storage_pb_delta',
            'storage_pb_delta_pct',
            'active_tenants',
            'active_tenants_delta',
            'active_tenants_delta_pct',
            'alarm_count',
            'alarm_count_delta',
            'alarm_count_delta_pct',
            'alarm_critical_count',
            'alarm_high_count',
            'alarm_low_count',
            'alarm_compute_members_count',
            'alarm_storage_members_count',
            'alarm_network_members_count',
            'alarm_virtual_machines_count',
            'bandwidth_gbps',
            'bandwidth_gbps_delta',
            'bandwidth_gbps_delta_pct',
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
    'active_clouds' => 'integer',
    'active_clouds_delta' => 'integer',
    'compute_vcpus' => 'integer',
    'compute_vcpus_delta' => 'integer',
    'active_tenants' => 'integer',
    'active_tenants_delta' => 'integer',
    'alarm_count' => 'integer',
    'alarm_count_delta' => 'integer',
    'alarm_critical_count' => 'integer',
    'alarm_high_count' => 'integer',
    'alarm_low_count' => 'integer',
    'alarm_compute_members_count' => 'integer',
    'alarm_storage_members_count' => 'integer',
    'alarm_network_members_count' => 'integer',
    'alarm_virtual_machines_count' => 'integer',
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
        parent::observe(KpiPerformanceObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_kpi_performance');

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
