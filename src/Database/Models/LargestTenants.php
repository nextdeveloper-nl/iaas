<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\LargestTenantsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * LargestTenants model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $iaas_account_id
 * @property string $iaas_account_uuid
 * @property integer $iam_account_id
 * @property integer $vm_count
 * @property integer $vcpu_total
 * @property integer $ram_total_gb
 * @property integer $disk_count
 * @property $storage_gb
 * @property integer $network_count
 * @property $bandwidth_gbps
 */
class LargestTenants extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_largest_tenants';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iaas_account_id',
            'iaas_account_uuid',
            'iam_account_id',
            'vm_count',
            'vcpu_total',
            'ram_total_gb',
            'disk_count',
            'storage_gb',
            'network_count',
            'bandwidth_gbps',
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
    'iaas_account_id' => 'integer',
    'vm_count' => 'integer',
    'vcpu_total' => 'integer',
    'ram_total_gb' => 'integer',
    'disk_count' => 'integer',
    'network_count' => 'integer',
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
        parent::observe(LargestTenantsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_largest_tenants');

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
