<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\VirtualDiskImagesObserver;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;
use NextDeveloper\Commons\Database\Traits\HasObject;

/**
 * VirtualDiskImages model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property integer $size
 * @property integer $physical_utilisation
 * @property $available_operations
 * @property $current_operations
 * @property boolean $is_cdrom
 * @property string $hypervisor_uuid
 * @property $hypervisor_data
 * @property integer $iaas_storage_volume_id
 * @property integer $iaas_virtual_machine_id
 * @property integer $device_number
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property boolean $is_draft
 * @property integer $iaas_repository_image_id
 * @property integer $iaas_storage_pool_id
 * @property $vbd_hypervisor_data
 * @property string $vbd_hypervisor_uuid
 * @property $utilization
 */
class VirtualDiskImages extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_virtual_disk_images';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'size',
            'physical_utilisation',
            'available_operations',
            'current_operations',
            'is_cdrom',
            'hypervisor_uuid',
            'hypervisor_data',
            'iaas_storage_volume_id',
            'iaas_virtual_machine_id',
            'device_number',
            'iam_account_id',
            'iam_user_id',
            'is_draft',
            'iaas_repository_image_id',
            'iaas_storage_pool_id',
            'vbd_hypervisor_data',
            'vbd_hypervisor_uuid',
            'utilization',
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
    'size' => 'integer',
    'physical_utilisation' => 'integer',
    'available_operations' => 'array',
    'current_operations' => 'array',
    'is_cdrom' => 'boolean',
    'hypervisor_uuid' => 'string',
    'hypervisor_data' => 'array',
    'iaas_storage_volume_id' => 'integer',
    'iaas_virtual_machine_id' => 'integer',
    'device_number' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_draft' => 'boolean',
    'iaas_repository_image_id' => 'integer',
    'iaas_storage_pool_id' => 'integer',
    'vbd_hypervisor_data' => 'array',
    'vbd_hypervisor_uuid' => 'string',
    'utilization' => 'double',
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
        parent::observe(VirtualDiskImagesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_disk_images');

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
