<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\SSHable;
use NextDeveloper\IAAS\Database\Observers\RepositoriesObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\IAAS\Database\Traits\Agentable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * Repositories model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $ssh_username
 * @property string $ssh_password
 * @property $ip_addr
 * @property boolean $is_active
 * @property boolean $is_public
 * @property string $last_hash
 * @property boolean $is_vm_repo
 * @property boolean $is_iso_repo
 * @property boolean $is_docker_registry
 * @property string $iso_path
 * @property string $vm_path
 * @property integer $docker_registry_port
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property $local_ip_addr
 * @property boolean $is_behind_firewall
 * @property boolean $is_management_agent_available
 * @property integer $ssh_port
 */
class Repositories extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;
    use SSHable, Agentable;

    public $timestamps = true;

    protected $table = 'iaas_repositories';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'ssh_username',
            'ssh_password',
            'ip_addr',
            'is_active',
            'is_public',
            'last_hash',
            'is_vm_repo',
            'is_iso_repo',
            'is_docker_registry',
            'iso_path',
            'vm_path',
            'docker_registry_port',
            'iam_account_id',
            'iam_user_id',
            'local_ip_addr',
            'is_behind_firewall',
            'is_management_agent_available',
            'ssh_port',
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
    'ssh_username' => 'string',
    'ssh_password' => 'string',
    'is_active' => 'boolean',
    'is_public' => 'boolean',
    'last_hash' => 'string',
    'is_vm_repo' => 'boolean',
    'is_iso_repo' => 'boolean',
    'is_docker_registry' => 'boolean',
    'iso_path' => 'string',
    'vm_path' => 'string',
    'docker_registry_port' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_behind_firewall' => 'boolean',
    'is_management_agent_available' => 'boolean',
    'ssh_port' => 'integer',
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
        parent::observe(RepositoriesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_repositories');

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

    public function accounts() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Accounts::class);
    }
    
    public function users() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    protected function sshPassword(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: function ($value) {
                return encrypt($value);
            },
        );
    }













}
