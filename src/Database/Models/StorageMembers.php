<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\StorageMembersObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\SSHable;
use NextDeveloper\IAAS\Database\Traits\Agentable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * StorageMembers model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $hostname
 * @property string $ip_addr
 * @property string $local_ip_addr
 * @property $management_data
 * @property string $configuration_data
 * @property boolean $is_healthy
 * @property boolean $has_warning
 * @property boolean $has_error
 * @property $features
 * @property boolean $is_behind_firewall
 * @property integer $total_socket
 * @property integer $total_cpu
 * @property integer $total_ram
 * @property integer $total_disk
 * @property integer $used_disk
 * @property $disk_info
 * @property \Carbon\Carbon $uptime
 * @property \Carbon\Carbon $idle_time
 * @property integer $benchmark_score
 * @property boolean $is_maintenance
 * @property boolean $is_alive
 * @property integer $iaas_storage_pool_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property array $tags
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $ssh_username
 * @property string $ssh_password
 * @property integer $ssh_port
 */
class StorageMembers extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;
    use SSHable, Agentable;

    public $timestamps = true;

    protected $table = 'iaas_storage_members';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'hostname',
            'ip_addr',
            'local_ip_addr',
            'management_data',
            'configuration_data',
            'is_healthy',
            'has_warning',
            'has_error',
            'features',
            'is_behind_firewall',
            'total_socket',
            'total_cpu',
            'total_ram',
            'total_disk',
            'used_disk',
            'disk_info',
            'uptime',
            'idle_time',
            'benchmark_score',
            'is_maintenance',
            'is_alive',
            'iaas_storage_pool_id',
            'iam_account_id',
            'iam_user_id',
            'tags',
            'ssh_username',
            'ssh_password',
            'ssh_port',
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
    'hostname' => 'string',
    'ip_addr' => 'string',
    'local_ip_addr' => 'string',
    'management_data' => 'array',
    'configuration_data' => 'string',
    'is_healthy' => 'boolean',
    'has_warning' => 'boolean',
    'has_error' => 'boolean',
    'features' => 'array',
    'is_behind_firewall' => 'boolean',
    'total_socket' => 'integer',
    'total_cpu' => 'integer',
    'total_ram' => 'integer',
    'total_disk' => 'integer',
    'used_disk' => 'integer',
    'disk_info' => 'array',
    'uptime' => 'datetime',
    'idle_time' => 'datetime',
    'benchmark_score' => 'integer',
    'is_maintenance' => 'boolean',
    'is_alive' => 'boolean',
    'iaas_storage_pool_id' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'ssh_username' => 'string',
    'ssh_password' => 'string',
    'ssh_port' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'uptime',
    'idle_time',
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
        parent::observe(StorageMembersObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_storage_members');

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

    public function storageMemberStats() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\StorageMemberStats::class);
    }

    public function computeMemberStorageVolumes() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\ComputeMemberStorageVolumes::class);
    }

    public function storageVolumes() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\StorageVolumes::class);
    }

    public function storageMemberDevices() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\StorageMemberDevices::class);
    }

    public function storagePools() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\StoragePools::class);
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
