<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\DockerContainersObserver;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;
use NextDeveloper\Commons\Database\Traits\HasObject;

/**
 * DockerContainers model. A container hosted on a VirtualMachines record
 * whose agent has reported a "docker" capability (see
 * VirtualMachines::getIsDockerHostAttribute()). All lifecycle commands are
 * dispatched through the parent VM's own agent identity - this model does
 * not carry agent credentials of its own.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property integer $iaas_virtual_machine_id
 * @property string $image
 * @property string $status
 * @property string $container_id
 * @property $command
 * @property $env_vars
 * @property $ports
 * @property string $restart_policy
 * @property float $cpu_limit
 * @property integer $memory_limit_mb
 * @property \Carbon\Carbon $last_agent_sync_at
 * @property string $agent_error
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class DockerContainers extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_docker_containers';

    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'iaas_virtual_machine_id',
            'image',
            'status',
            'container_id',
            'command',
            'env_vars',
            'ports',
            'restart_policy',
            'cpu_limit',
            'memory_limit_mb',
            'last_agent_sync_at',
            'agent_error',
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
    'iaas_virtual_machine_id' => 'integer',
    'image' => 'string',
    'status' => 'string',
    'container_id' => 'string',
    'command' => 'array',
    'env_vars' => 'array',
    'ports' => 'array',
    'restart_policy' => 'string',
    'cpu_limit' => 'float',
    'memory_limit_mb' => 'integer',
    'last_agent_sync_at' => 'datetime',
    'agent_error' => 'string',
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
    'last_agent_sync_at',
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
        parent::observe(DockerContainersObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_docker_containers');

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

    public function virtualMachine() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\VirtualMachines::class, 'iaas_virtual_machine_id');
    }

    public function accounts() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Accounts::class, 'iam_account_id');
    }

    public function users() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class, 'iam_user_id');
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
