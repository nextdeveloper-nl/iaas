<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\ComputePoolsObserver;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * ComputePools model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $resource_validator
 * @property $pool_data
 * @property string $virtualization
 * @property string $provisioning_alg
 * @property boolean $is_active
 * @property boolean $is_alive
 * @property boolean $is_public
 * @property integer $iaas_datacenter_id
 * @property integer $iaas_cloud_node_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property array $tags
 * @property $price_pergb
 * @property integer $common_currency_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $pool_type
 * @property integer $total_cpu
 * @property integer $total_ram
 * @property $price_pergb_month
 * @property $disk_ram_ratio
 * @property string $code_name
 * @property boolean $is_default
 * @property boolean $is_iso27001_enabled
 */
class ComputePools extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_compute_pools';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'resource_validator',
            'pool_data',
            'virtualization',
            'provisioning_alg',
            'is_active',
            'is_alive',
            'is_public',
            'iaas_datacenter_id',
            'iaas_cloud_node_id',
            'iam_account_id',
            'iam_user_id',
            'tags',
            'price_pergb',
            'common_currency_id',
            'pool_type',
            'total_cpu',
            'total_ram',
            'price_pergb_month',
            'disk_ram_ratio',
            'code_name',
            'is_default',
            'is_iso27001_enabled',
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
    'resource_validator' => 'string',
    'pool_data' => 'array',
    'virtualization' => 'string',
    'provisioning_alg' => 'string',
    'is_active' => 'boolean',
    'is_alive' => 'boolean',
    'is_public' => 'boolean',
    'iaas_datacenter_id' => 'integer',
    'iaas_cloud_node_id' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'common_currency_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'pool_type' => 'string',
    'total_cpu' => 'integer',
    'total_ram' => 'integer',
    'code_name' => 'string',
    'is_default' => 'boolean',
    'is_iso27001_enabled' => 'boolean',
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
        parent::observe(ComputePoolsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_pools');

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

    public function cloudNodes() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\CloudNodes::class);
    }
    
    public function datacenters() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\Datacenters::class);
    }
    
    public function computeMembers() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\ComputeMembers::class);
    }

    public function virtualMachines() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\VirtualMachines::class);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE







}
