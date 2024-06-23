<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\ComputePoolsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * ComputePools model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $resource_validator
 * @property $pool_data
 * @property string $virtualization
 * @property string $provisioning_alg
 * @property boolean $is_active
 * @property boolean $is_alive
 * @property boolean $is_public
 * @property integer $iaas_datacenter_id
 * @property integer $iaas_cloud_node_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property array $tags
 * @property $price_pergb
 * @property integer $common_currency_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $pool_type
 */
class ComputePools extends Model
{
    use Filterable, UuidId, CleanCache, Taggable;
    use SoftDeletes;


    public $timestamps = true;

    protected $table = 'iaas_compute_pools';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'resource_validator',
            'pool_data',
            'virtualization',
            'provisioning_alg',
            'is_active',
            'is_alive',
            'is_public',
            'iaas_datacenter_id',
            'iaas_cloud_node_id',
            'iam_account_id',
            'iam_user_id',
            'tags',
            'price_pergb',
            'common_currency_id',
            'pool_type',
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
    'resource_validator' => 'string',
    'pool_data' => 'array',
    'virtualization' => 'string',
    'provisioning_alg' => 'string',
    'is_active' => 'boolean',
    'is_alive' => 'boolean',
    'is_public' => 'boolean',
    'iaas_datacenter_id' => 'integer',
    'iaas_cloud_node_id' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'common_currency_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'pool_type' => 'string',
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
        parent::observe(ComputePoolsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_pools');

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
