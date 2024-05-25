<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\StorageVolumesObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * StorageVolumes model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $hypervisor_uuid
 * @property string $name
 * @property string $disk_physical_type
 * @property $connection_parameters
 * @property integer $total_hdd
 * @property integer $used_hdd
 * @property integer $free_hdd
 * @property integer $virtual_allocation
 * @property boolean $is_storage
 * @property boolean $is_repo
 * @property boolean $is_cdrom
 * @property $hypervisor_data
 * @property integer $iaas_storage_pool_id
 * @property integer $iaas_storage_member_id
 * @property boolean $is_alive
 * @property array $tags
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class StorageVolumes extends Model
{
    use Filterable, UuidId, CleanCache, Taggable;
    use SoftDeletes;


    public $timestamps = true;

    protected $table = 'iaas_storage_volumes';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'hypervisor_uuid',
            'name',
            'disk_physical_type',
            'connection_parameters',
            'total_hdd',
            'used_hdd',
            'free_hdd',
            'virtual_allocation',
            'is_storage',
            'is_repo',
            'is_cdrom',
            'hypervisor_data',
            'iaas_storage_pool_id',
            'iaas_storage_member_id',
            'iaas_storage_pool_id',
            'is_alive',
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
    'disk_physical_type' => 'string',
    'connection_parameters' => 'array',
    'total_hdd' => 'integer',
    'used_hdd' => 'integer',
    'free_hdd' => 'integer',
    'virtual_allocation' => 'integer',
    'is_storage' => 'boolean',
    'is_repo' => 'boolean',
    'is_cdrom' => 'boolean',
    'hypervisor_data' => 'array',
    'iaas_storage_pool_id' => 'integer',
    'iaas_storage_member_id' => 'integer',
    'is_alive' => 'boolean',
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
        parent::observe(StorageVolumesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_storage_volumes');

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
