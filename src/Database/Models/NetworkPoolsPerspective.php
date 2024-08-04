<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\NetworkPoolsPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NetworkPoolsPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $resource_validator
 * @property boolean $is_active
 * @property integer $vlan_start
 * @property integer $vlan_end
 * @property integer $vxlan_start
 * @property integer $vxlan_end
 * @property string $provisioning_alg
 * @property $price_pergb
 * @property string $currency
 * @property integer $total_networks
 * @property string $datacenter
 * @property string $cloud_node
 * @property string $maintainer
 * @property string $responsible
 * @property array $tags
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class NetworkPoolsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_network_pools_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'resource_validator',
            'is_active',
            'vlan_start',
            'vlan_end',
            'vxlan_start',
            'vxlan_end',
            'provisioning_alg',
            'price_pergb',
            'currency',
            'total_networks',
            'datacenter',
            'cloud_node',
            'maintainer',
            'responsible',
            'tags',
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
    'name' => 'string',
    'resource_validator' => 'string',
    'is_active' => 'boolean',
    'vlan_start' => 'integer',
    'vlan_end' => 'integer',
    'vxlan_start' => 'integer',
    'vxlan_end' => 'integer',
    'provisioning_alg' => 'string',
    'currency' => 'string',
    'total_networks' => 'integer',
    'datacenter' => 'string',
    'cloud_node' => 'string',
    'maintainer' => 'string',
    'responsible' => 'string',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
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
        parent::observe(NetworkPoolsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_network_pools_perspective');

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
