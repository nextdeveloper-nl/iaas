<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\DatacentersObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * Datacenters model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property boolean $is_public
 * @property boolean $is_active
 * @property boolean $maintenance_mode
 * @property string $geo_latitude
 * @property string $geo_longitude
 * @property integer $tier_level
 * @property $total_capacity
 * @property $guaranteed_uptime
 * @property boolean $is_carrier_neutral
 * @property string $power_source
 * @property string $ups
 * @property string $cooling
 * @property integer $common_city_id
 * @property integer $iam_user_id
 * @property integer $iam_account_id
 * @property integer $common_country_id
 * @property array $tags
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $description
 */
class Datacenters extends Model
{
    use Filterable, UuidId, CleanCache, Taggable;
    use SoftDeletes;


    public $timestamps = true;

    protected $table = 'iaas_datacenters';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'slug',
            'is_public',
            'is_active',
            'maintenance_mode',
            'geo_latitude',
            'geo_longitude',
            'tier_level',
            'total_capacity',
            'guaranteed_uptime',
            'is_carrier_neutral',
            'power_source',
            'ups',
            'cooling',
            'common_city_id',
            'iam_user_id',
            'iam_account_id',
            'common_country_id',
            'tags',
            'description',
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
    'is_public' => 'boolean',
    'is_active' => 'boolean',
    'maintenance_mode' => 'boolean',
    'geo_latitude' => 'string',
    'geo_longitude' => 'string',
    'tier_level' => 'integer',
    'total_capacity' => 'array',
    'is_carrier_neutral' => 'boolean',
    'power_source' => 'string',
    'ups' => 'string',
    'cooling' => 'string',
    'common_city_id' => 'integer',
    'common_country_id' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'description' => 'string',
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
        parent::observe(DatacentersObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_datacenters');

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
