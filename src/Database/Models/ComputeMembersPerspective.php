<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\SSHable;
use NextDeveloper\IAAS\Database\Traits\Agentable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\ComputeMembersPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * ComputeMembersPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $hostname
 * @property $ip_addr
 * @property boolean $has_warning
 * @property boolean $has_error
 * @property string $ssh_username
 * @property string $ssh_password
 * @property integer $ssh_port
 * @property integer $total_socket
 * @property integer $total_cpu
 * @property integer $total_ram
 * @property integer $used_cpu
 * @property integer $used_ram
 * @property integer $free_cpu
 * @property integer $running_vm
 * @property integer $halted_vm
 * @property integer $total_vm
 * @property \Carbon\Carbon $uptime
 * @property \Carbon\Carbon $idle_time
 * @property integer $benchmark_score
 * @property boolean $is_in_maintenance
 * @property boolean $is_alive
 * @property string $compute_pool_name
 * @property integer $iaas_compute_pool_id
 * @property string $maintainer
 * @property string $responsible
 * @property $states
 * @property array $tags
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 */
class ComputeMembersPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SSHable, Agentable;

    public $timestamps = false;

    protected $table = 'iaas_compute_members_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'hostname',
            'ip_addr',
            'has_warning',
            'has_error',
            'ssh_username',
            'ssh_password',
            'ssh_port',
            'total_socket',
            'total_cpu',
            'total_ram',
            'used_cpu',
            'used_ram',
            'free_cpu',
            'running_vm',
            'halted_vm',
            'total_vm',
            'uptime',
            'idle_time',
            'benchmark_score',
            'is_in_maintenance',
            'is_alive',
            'compute_pool_name',
            'iaas_compute_pool_id',
            'maintainer',
            'responsible',
            'states',
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
    'hostname' => 'string',
    'has_warning' => 'boolean',
    'has_error' => 'boolean',
    'ssh_username' => 'string',
    'ssh_password' => 'string',
    'ssh_port' => 'integer',
    'total_socket' => 'integer',
    'total_cpu' => 'integer',
    'total_ram' => 'integer',
    'used_cpu' => 'integer',
    'used_ram' => 'integer',
    'free_cpu' => 'integer',
    'running_vm' => 'integer',
    'halted_vm' => 'integer',
    'total_vm' => 'integer',
    'uptime' => 'datetime',
    'idle_time' => 'datetime',
    'benchmark_score' => 'integer',
    'is_in_maintenance' => 'boolean',
    'is_alive' => 'boolean',
    'compute_pool_name' => 'string',
    'iaas_compute_pool_id' => 'integer',
    'maintainer' => 'string',
    'responsible' => 'string',
    'states' => 'array',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'uptime',
    'idle_time',
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
        parent::observe(ComputeMembersPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_compute_members_perspective');

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
