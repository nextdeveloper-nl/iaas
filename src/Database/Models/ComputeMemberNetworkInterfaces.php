<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\ComputeMemberNetworkInterfacesObserver;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\HasObject;

/**
 * ComputeMemberNetworkInterfaces model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $device
 * @property $mac_addr
 * @property integer $vlan
 * @property integer $mtu
 * @property boolean $is_management
 * @property boolean $is_default
 * @property boolean $is_connected
 * @property $hypervisor_data
 * @property integer $iaas_compute_member_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property boolean $is_bridge
 * @property string $hypervisor_uuid
 * @property string $network_uuid
 * @property string $network_name
 * @property $vlan_data
 */
class ComputeMemberNetworkInterfaces extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_compute_member_network_interfaces';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'device',
            'mac_addr',
            'vlan',
            'mtu',
            'is_management',
            'is_default',
            'is_connected',
            'hypervisor_data',
            'iaas_compute_member_id',
            'iam_account_id',
            'iam_user_id',
            'is_bridge',
            'hypervisor_uuid',
            'network_uuid',
            'network_name',
            'vlan_data',
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
    'device' => 'string',
    'vlan' => 'integer',
    'mtu' => 'integer',
    'is_management' => 'boolean',
    'is_default' => 'boolean',
    'is_connected' => 'boolean',
    'hypervisor_data' => 'array',
    'iaas_compute_member_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_bridge' => 'boolean',
    'hypervisor_uuid' => 'string',
    'network_uuid' => 'string',
    'network_name' => 'string',
    'vlan_data' => 'array',
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
        parent::observe(ComputeMemberNetworkInterfacesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_member_network_interfaces');

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

    public function accounts() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Accounts::class);
    }
    
    public function computeMembers() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\ComputeMembers::class);
    }
    
    public function users() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE



































}
