<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\CloudNodesPerformanceObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * CloudNodesPerformance model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property boolean $is_active
 * @property boolean $is_alive
 * @property boolean $is_in_maintenance
 * @property string $datacenter_name
 * @property integer $vm_count
 * @property integer $compute_vcpu_total
 * @property integer $compute_vcpu_used
 * @property $compute_vcpu_pct
 * @property string $compute_vcpu_health
 * @property integer $compute_alarm_count
 * @property $memory_total_gb
 * @property $memory_used_gb
 * @property $memory_pct
 * @property string $memory_health
 * @property integer $storage_total_gb
 * @property integer $storage_used_gb
 * @property $storage_pct
 * @property string $storage_health
 * @property integer $storage_alarm_count
 * @property integer $network_alarm_count
 * @property string $network_health
 */
class CloudNodesPerformance extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_cloud_nodes_performance';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'is_active',
            'is_alive',
            'is_in_maintenance',
            'datacenter_name',
            'vm_count',
            'compute_vcpu_total',
            'compute_vcpu_used',
            'compute_vcpu_pct',
            'compute_vcpu_health',
            'compute_alarm_count',
            'memory_total_gb',
            'memory_used_gb',
            'memory_pct',
            'memory_health',
            'storage_total_gb',
            'storage_used_gb',
            'storage_pct',
            'storage_health',
            'storage_alarm_count',
            'network_alarm_count',
            'network_health',
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
    'name' => 'string',
    'is_active' => 'boolean',
    'is_alive' => 'boolean',
    'is_in_maintenance' => 'boolean',
    'datacenter_name' => 'string',
    'vm_count' => 'integer',
    'compute_vcpu_total' => 'integer',
    'compute_vcpu_used' => 'integer',
    'compute_vcpu_health' => 'string',
    'compute_alarm_count' => 'integer',
    'memory_health' => 'string',
    'storage_total_gb' => 'integer',
    'storage_used_gb' => 'integer',
    'storage_health' => 'string',
    'storage_alarm_count' => 'integer',
    'network_alarm_count' => 'integer',
    'network_health' => 'string',
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
        parent::observe(CloudNodesPerformanceObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_cloud_nodes_performance');

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
