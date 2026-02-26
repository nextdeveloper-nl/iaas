<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\ComputeMemberStorageVolumesPerspectiveObserver;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;
use NextDeveloper\Commons\Database\Traits\HasObject;

/**
 * ComputeMemberStorageVolumesPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $volume_name
 * @property integer $iaas_storage_volume_id
 * @property string $storage_pool_name
 * @property integer $iaas_storage_pool_id
 * @property string $storage_member_name
 * @property integer $iaas_storage_member_id
 * @property string $compute_member_name
 * @property integer $iaas_compute_member_id
 * @property string $maintainer
 * @property string $responsible
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property integer $used_hdd
 * @property integer $free_hdd
 * @property string $disk_physical_type
 * @property boolean $is_storage
 * @property boolean $is_alive
 * @property boolean $is_cdrom
 * @property integer $total_hdd
 * @property integer $virtual_allocation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class ComputeMemberStorageVolumesPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_compute_member_storage_volumes_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'volume_name',
            'iaas_storage_volume_id',
            'storage_pool_name',
            'iaas_storage_pool_id',
            'storage_member_name',
            'iaas_storage_member_id',
            'compute_member_name',
            'iaas_compute_member_id',
            'maintainer',
            'responsible',
            'iam_account_id',
            'iam_user_id',
            'used_hdd',
            'free_hdd',
            'disk_physical_type',
            'is_storage',
            'is_alive',
            'is_cdrom',
            'total_hdd',
            'virtual_allocation',
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
    'description' => 'string',
    'volume_name' => 'string',
    'iaas_storage_volume_id' => 'integer',
    'storage_pool_name' => 'string',
    'iaas_storage_pool_id' => 'integer',
    'storage_member_name' => 'string',
    'iaas_storage_member_id' => 'integer',
    'compute_member_name' => 'string',
    'iaas_compute_member_id' => 'integer',
    'maintainer' => 'string',
    'responsible' => 'string',
    'used_hdd' => 'integer',
    'free_hdd' => 'integer',
    'disk_physical_type' => 'string',
    'is_storage' => 'boolean',
    'is_alive' => 'boolean',
    'is_cdrom' => 'boolean',
    'total_hdd' => 'integer',
    'virtual_allocation' => 'integer',
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
        parent::observe(ComputeMemberStorageVolumesPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_member_storage_volumes_perspective');

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
