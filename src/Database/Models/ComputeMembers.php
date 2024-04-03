<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\ComputeMembersObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * ComputeMembers model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $hostname
 * @property string $ip_addr
 * @property string $local_ip_addr
 * @property $management_data
 * @property $features
 * @property boolean $is_behind_firewall
 * @property string $hypervisor_uuid
 * @property string $hypervisor_data
 * @property integer $total_socket
 * @property integer $total_cpu
 * @property integer $total_ram
 * @property integer $used_cpu
 * @property integer $used_ram
 * @property integer $total_vm
 * @property integer $max_overbooking_ratio
 * @property $cpu_info
 * @property \Carbon\Carbon $uptime
 * @property \Carbon\Carbon $idle_time
 * @property integer $benchmark_score
 * @property boolean $is_maintenance
 * @property boolean $is_alive
 * @property integer $iaas_compute_pool_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property array $tags
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class ComputeMembers extends Model
{
    use Filterable, UuidId, CleanCache, Taggable;
    use SoftDeletes;


    public $timestamps = true;

    protected $table = 'iaas_compute_members';


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
            'features',
            'is_behind_firewall',
            'hypervisor_uuid',
            'hypervisor_data',
            'total_socket',
            'total_cpu',
            'total_ram',
            'used_cpu',
            'used_ram',
            'total_vm',
            'max_overbooking_ratio',
            'cpu_info',
            'uptime',
            'idle_time',
            'benchmark_score',
            'is_maintenance',
            'is_alive',
            'iaas_compute_pool_id',
            'iam_account_id',
            'iam_user_id',
            'tags',
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
    'features' => 'array',
    'is_behind_firewall' => 'boolean',
    'hypervisor_data' => 'string',
    'total_socket' => 'integer',
    'total_cpu' => 'integer',
    'total_ram' => 'integer',
    'used_cpu' => 'integer',
    'used_ram' => 'integer',
    'total_vm' => 'integer',
    'max_overbooking_ratio' => 'integer',
    'cpu_info' => 'array',
    'uptime' => 'datetime',
    'idle_time' => 'datetime',
    'benchmark_score' => 'integer',
    'is_maintenance' => 'boolean',
    'is_alive' => 'boolean',
    'iaas_compute_pool_id' => 'integer',
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
        parent::observe(ComputeMembersObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_members');

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
