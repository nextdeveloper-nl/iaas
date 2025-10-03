<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\SSHable;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\NetworkMembersObserver;
use NextDeveloper\IAAS\Database\Traits\Agentable;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * NetworkMembers model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property $ip_addr
 * @property string $ssh_username
 * @property string $ssh_password
 * @property integer $iaas_network_pool_id
 * @property array $tags
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property integer $ssh_port
 * @property $local_ip_addr
 * @property boolean $is_behind_firewall
 * @property string $switch_type
 * @property boolean $is_root_switch
 */
class NetworkMembers extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator;
    use SoftDeletes;
    use SSHable, Agentable;

    public $timestamps = true;

    protected $table = 'iaas_network_members';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'ip_addr',
            'ssh_username',
            'ssh_password',
            'iaas_network_pool_id',
            'tags',
            'iam_account_id',
            'iam_user_id',
            'ssh_port',
            'local_ip_addr',
            'is_behind_firewall',
            'switch_type',
            'is_root_switch',
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
    'ssh_username' => 'string',
    'ssh_password' => 'string',
    'iaas_network_pool_id' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'ssh_port' => 'integer',
    'is_behind_firewall' => 'boolean',
    'switch_type' => 'string',
    'is_root_switch' => 'boolean',
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
        parent::observe(NetworkMembersObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_network_members');

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

    public function networkMemberDevices() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\NetworkMemberDevices::class);
    }

    public function networkMembersInterfaces() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces::class);
    }

    public function accounts() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Accounts::class);
    }
    
    public function networkPools() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\NetworkPools::class);
    }
    
    public function users() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


    protected function sshPassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: function ($value) {
                return encrypt($value);
            },
        );
    }









































}
