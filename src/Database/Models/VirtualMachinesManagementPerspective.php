<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VirtualMachinesManagementPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VirtualMachinesManagementPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $hypervisor_uuid
 * @property string $hypervisor_name_label
 * @property $ip_addr
 * @property string $disk_name
 * @property string $disk_hypervisor_uuid
 * @property string $storage_volume_name
 * @property string $storage_volume_hypervisor_uuid
 * @property string $compute_member_name
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property integer $iaas_compute_member_id
 * @property integer $iaas_compute_pool_id
 * @property integer $iaas_cloud_node_id
 */
class VirtualMachinesManagementPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_virtual_machines_management_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'hypervisor_uuid',
            'hypervisor_name_label',
            'ip_addr',
            'disk_name',
            'disk_hypervisor_uuid',
            'storage_volume_name',
            'storage_volume_hypervisor_uuid',
            'compute_member_name',
            'iam_account_id',
            'iam_user_id',
            'iaas_compute_member_id',
            'iaas_compute_pool_id',
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
    'hypervisor_name_label' => 'string',
    'disk_name' => 'string',
    'disk_hypervisor_uuid' => 'string',
    'storage_volume_name' => 'string',
    'storage_volume_hypervisor_uuid' => 'string',
    'compute_member_name' => 'string',
    'iaas_compute_member_id' => 'integer',
    'iaas_compute_pool_id' => 'integer',
    'iaas_cloud_node_id' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [

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
        parent::observe(VirtualMachinesManagementPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_machines_management_perspective');

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
