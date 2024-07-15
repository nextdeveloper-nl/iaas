<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\StorageMembersPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * StorageMembersPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $hostname
 * @property string $ip_addr
 * @property string $local_ip_addr
 * @property boolean $is_healthy
 * @property boolean $has_warning
 * @property boolean $has_error
 * @property integer $total_disk
 * @property integer $used_disk
 * @property \Carbon\Carbon $uptime
 * @property boolean $is_maintenance
 * @property boolean $is_alive
 * @property $storage_pool
 * @property integer $iaas_storage_pool_id
 * @property string $maintainer
 * @property string $responsible
 */
class StorageMembersPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;

    public $timestamps = false;

    protected $table = 'iaas_storage_members_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'hostname',
            'ip_addr',
            'local_ip_addr',
            'is_healthy',
            'has_warning',
            'has_error',
            'total_disk',
            'used_disk',
            'uptime',
            'is_maintenance',
            'is_alive',
            'storage_pool',
            'iaas_storage_pool_id',
            'maintainer',
            'responsible',
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
    'is_healthy' => 'boolean',
    'has_warning' => 'boolean',
    'has_error' => 'boolean',
    'total_disk' => 'integer',
    'used_disk' => 'integer',
    'uptime' => 'datetime',
    'is_maintenance' => 'boolean',
    'is_alive' => 'boolean',
    'iaas_storage_pool_id' => 'integer',
    'maintainer' => 'string',
    'responsible' => 'string',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'uptime',
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
        parent::observe(StorageMembersPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_storage_members_perspective');

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
