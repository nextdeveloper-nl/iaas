<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\ComputeMemberStorageVolumesObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * ComputeMemberStorageVolumes model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $hypervisor_uuid
 * @property $hypervisor_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $name
 * @property string $description
 * @property $block_device_data
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property integer $iaas_storage_volume_id
 * @property integer $iaas_storage_member_id
 * @property integer $iaas_storage_pool_id
 * @property integer $iaas_compute_member_id
 * @property boolean $is_local_storage
 */
class ComputeMemberStorageVolumes extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_compute_member_storage_volumes';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'hypervisor_uuid',
            'hypervisor_data',
            'name',
            'description',
            'block_device_data',
            'iam_account_id',
            'iam_user_id',
            'iaas_storage_volume_id',
            'iaas_storage_member_id',
            'iaas_storage_pool_id',
            'iaas_compute_member_id',
            'is_local_storage',
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
    'hypervisor_uuid' => 'string',
    'hypervisor_data' => 'array',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'name' => 'string',
    'description' => 'string',
    'block_device_data' => 'array',
    'iaas_storage_volume_id' => 'integer',
    'iaas_storage_member_id' => 'integer',
    'iaas_storage_pool_id' => 'integer',
    'iaas_compute_member_id' => 'integer',
    'is_local_storage' => 'boolean',
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
        parent::observe(ComputeMemberStorageVolumesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_member_storage_volumes');

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

    public function storageVolumes() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\StorageVolumes::class);
    }
    
    public function storageMembers() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\StorageMembers::class);
    }
    
    public function storagePools() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\StoragePools::class);
    }
    
    public function computeMembers() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\ComputeMembers::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE




}
