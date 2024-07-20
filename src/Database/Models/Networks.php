<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\NetworksObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * Networks model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property integer $vlan
 * @property string $vxlan
 * @property integer $bandwidth
 * @property boolean $is_public
 * @property boolean $is_vpn
 * @property boolean $is_management
 * @property $ip_addr
 * @property $ip_addr_range_start
 * @property $ip_addr_range_end
 * @property array $dns_nameservers
 * @property integer $mtu
 * @property integer $common_domain_id
 * @property integer $iaas_dhcp_server_id
 * @property integer $iaas_gateway_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property boolean $is_dmz
 * @property $price_pergb
 * @property $price_perip
 * @property integer $speed_limit
 * @property integer $iaas_network_pool_id
 * @property integer $iaas_cloud_node_id
 */
class Networks extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_networks';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'vlan',
            'vxlan',
            'bandwidth',
            'is_public',
            'is_vpn',
            'is_management',
            'ip_addr',
            'ip_addr_range_start',
            'ip_addr_range_end',
            'dns_nameservers',
            'mtu',
            'common_domain_id',
            'iaas_dhcp_server_id',
            'iaas_gateway_id',
            'iam_account_id',
            'iam_user_id',
            'is_dmz',
            'price_pergb',
            'price_perip',
            'speed_limit',
            'iaas_network_pool_id',
            'iaas_cloud_node_id',
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
    'vlan' => 'integer',
    'vxlan' => 'string',
    'bandwidth' => 'integer',
    'is_public' => 'boolean',
    'is_vpn' => 'boolean',
    'is_management' => 'boolean',
    'mtu' => 'integer',
    'common_domain_id' => 'integer',
    'iaas_dhcp_server_id' => 'integer',
    'iaas_gateway_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_dmz' => 'boolean',
    'speed_limit' => 'integer',
    'iaas_network_pool_id' => 'integer',
    'iaas_cloud_node_id' => 'integer',
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
        parent::observe(NetworksObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_networks');

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
