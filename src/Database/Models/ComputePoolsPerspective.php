<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\ComputePoolsPerspectiveObserver;

/**
 * ComputePoolsPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $virtualization
 * @property string $resource_validator
 * @property boolean $is_active
 * @property $price_pergb
 * @property string $currency
 * @property integer $total_ram_in_pool
 * @property integer $total_cpu_in_pool
 * @property integer $used_ram_in_pool
 * @property integer $used_cpu_in_pool
 * @property integer $total_vm_in_pool
 * @property integer $running_ram_in_pool
 * @property integer $halted_ram_in_pool
 * @property string $maintainer
 * @property string $responsible
 * @property array $tags
 * @property string $pool_type
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class ComputePoolsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_compute_pools_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'virtualization',
            'resource_validator',
            'is_active',
            'price_pergb',
            'currency',
            'total_ram_in_pool',
            'total_cpu_in_pool',
            'used_ram_in_pool',
            'used_cpu_in_pool',
            'total_vm_in_pool',
            'running_ram_in_pool',
            'halted_ram_in_pool',
            'maintainer',
            'responsible',
            'tags',
            'pool_type',
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
    'virtualization' => 'string',
    'resource_validator' => 'string',
    'is_active' => 'boolean',
    'currency' => 'string',
    'total_ram_in_pool' => 'integer',
    'total_cpu_in_pool' => 'integer',
    'used_ram_in_pool' => 'integer',
    'used_cpu_in_pool' => 'integer',
    'total_vm_in_pool' => 'integer',
    'running_ram_in_pool' => 'integer',
    'halted_ram_in_pool' => 'integer',
    'maintainer' => 'string',
    'responsible' => 'string',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'pool_type' => 'string',
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
        parent::observe(ComputePoolsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_pools_perspective');

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
