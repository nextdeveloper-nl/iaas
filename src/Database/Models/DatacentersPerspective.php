<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\DatacentersPerspectiveObserver;

/**
 * DatacentersPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property boolean $is_public
 * @property boolean $is_active
 * @property boolean $is_in_maintenance
 * @property string $geo_latitude
 * @property string $geo_longitude
 * @property integer $tier_level
 * @property $total_capacity
 * @property $guaranteed_uptime
 * @property boolean $is_carrier_neutral
 * @property string $power_source
 * @property string $ups
 * @property string $cooling
 * @property string $city_name
 * @property string $country_name
 * @property integer $cloud_nodes_count
 * @property integer $compute_pools_count
 * @property integer $storage_pools_count
 * @property integer $network_pools_count
 * @property array $tags
 * @property string $datacenter_maintainer
 * @property string $maintainer
 * @property string $responsible
 * @property integer $iam_user_id
 * @property integer $iam_account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class DatacentersPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_datacenters_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'slug',
            'description',
            'is_public',
            'is_active',
            'is_in_maintenance',
            'geo_latitude',
            'geo_longitude',
            'tier_level',
            'total_capacity',
            'guaranteed_uptime',
            'is_carrier_neutral',
            'power_source',
            'ups',
            'cooling',
            'city_name',
            'country_name',
            'cloud_nodes_count',
            'compute_pools_count',
            'storage_pools_count',
            'network_pools_count',
            'tags',
            'datacenter_maintainer',
            'maintainer',
            'responsible',
            'iam_user_id',
            'iam_account_id',
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
    'slug' => 'string',
    'description' => 'string',
    'is_public' => 'boolean',
    'is_active' => 'boolean',
    'is_in_maintenance' => 'boolean',
    'geo_latitude' => 'string',
    'geo_longitude' => 'string',
    'tier_level' => 'integer',
    'total_capacity' => 'array',
    'is_carrier_neutral' => 'boolean',
    'power_source' => 'string',
    'ups' => 'string',
    'cooling' => 'string',
    'city_name' => 'string',
    'country_name' => 'string',
    'cloud_nodes_count' => 'integer',
    'compute_pools_count' => 'integer',
    'storage_pools_count' => 'integer',
    'network_pools_count' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'datacenter_maintainer' => 'string',
    'maintainer' => 'string',
    'responsible' => 'string',
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
        parent::observe(DatacentersPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_datacenters_perspective');

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
