<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\NetworkPoolsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * NetworkPools model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property integer $vlan_start
 * @property integer $vlan_end
 * @property integer $vxlan_start
 * @property integer $vxlan_end
 * @property boolean $is_vlan_available
 * @property boolean $is_vxlan_available
 * @property boolean $is_active
 * @property integer $iaas_datacenter_id
 * @property integer $iaas_cloud_node_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property string $provisioning_alg
 * @property string $resource_validator
 * @property array $tags
 * @property $price_pergb
 * @property integer $common_currency_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class NetworkPools extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_network_pools';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'vlan_start',
            'vlan_end',
            'vxlan_start',
            'vxlan_end',
            'is_vlan_available',
            'is_vxlan_available',
            'is_active',
            'iaas_datacenter_id',
            'iaas_cloud_node_id',
            'iam_account_id',
            'iam_user_id',
            'provisioning_alg',
            'resource_validator',
            'tags',
            'price_pergb',
            'common_currency_id',
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
    'vlan_start' => 'integer',
    'vlan_end' => 'integer',
    'vxlan_start' => 'integer',
    'vxlan_end' => 'integer',
    'is_vlan_available' => 'boolean',
    'is_vxlan_available' => 'boolean',
    'is_active' => 'boolean',
    'iaas_datacenter_id' => 'integer',
    'iaas_cloud_node_id' => 'integer',
    'provisioning_alg' => 'string',
    'resource_validator' => 'string',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'common_currency_id' => 'integer',
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
        parent::observe(NetworkPoolsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_network_pools');

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
