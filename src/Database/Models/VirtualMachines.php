<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\VirtualMachinesObserver;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VirtualMachines model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $username
 * @property string $password
 * @property string $hostname
 * @property string $description
 * @property string $os
 * @property string $distro
 * @property string $version
 * @property string $domain_type
 * @property string $status
 * @property integer $cpu
 * @property integer $ram
 * @property boolean $is_winrm_enabled
 * @property $available_operations
 * @property $current_operations
 * @property $blocked_operations
 * @property $console_data
 * @property boolean $is_snapshot
 * @property boolean $is_lost
 * @property boolean $is_locked
 * @property \Carbon\Carbon $last_metadata_request
 * @property $features
 * @property string $hypervisor_uuid
 * @property $hypervisor_data
 * @property integer $iaas_cloud_node_id
 * @property integer $iaas_compute_member_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property integer $template_id
 * @property array $tags
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property boolean $is_draft
 * @property integer $common_domain_id
 * @property string $lock_password
 * @property boolean $is_template
 * @property integer $iaas_repository_image_id
 * @property integer $iaas_compute_pool_id
 * @property string $auto_backup_interval
 * @property string $auto_backup_time
 * @property integer $snapshot_of_virtual_machine
 * @property integer $backup_repository_id
 */
class VirtualMachines extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_virtual_machines';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'username',
            'password',
            'hostname',
            'description',
            'os',
            'distro',
            'version',
            'domain_type',
            'status',
            'cpu',
            'ram',
            'is_winrm_enabled',
            'available_operations',
            'current_operations',
            'blocked_operations',
            'console_data',
            'is_snapshot',
            'is_lost',
            'is_locked',
            'last_metadata_request',
            'features',
            'hypervisor_uuid',
            'hypervisor_data',
            'iaas_cloud_node_id',
            'iaas_compute_member_id',
            'iam_account_id',
            'iam_user_id',
            'template_id',
            'tags',
            'is_draft',
            'common_domain_id',
            'lock_password',
            'is_template',
            'iaas_repository_image_id',
            'iaas_compute_pool_id',
            'auto_backup_interval',
            'auto_backup_time',
            'snapshot_of_virtual_machine',
            'backup_repository_id',
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
    'username' => 'string',
    'password' => 'string',
    'hostname' => 'string',
    'description' => 'string',
    'os' => 'string',
    'distro' => 'string',
    'version' => 'string',
    'domain_type' => 'string',
    'status' => 'string',
    'cpu' => 'integer',
    'ram' => 'integer',
    'is_winrm_enabled' => 'boolean',
    'available_operations' => 'array',
    'current_operations' => 'array',
    'blocked_operations' => 'array',
    'console_data' => 'array',
    'is_snapshot' => 'boolean',
    'is_lost' => 'boolean',
    'is_locked' => 'boolean',
    'last_metadata_request' => 'datetime',
    'features' => 'array',
    'hypervisor_data' => 'array',
    'iaas_cloud_node_id' => 'integer',
    'iaas_compute_member_id' => 'integer',
    'template_id' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_draft' => 'boolean',
    'common_domain_id' => 'integer',
    'lock_password' => 'string',
    'is_template' => 'boolean',
    'iaas_repository_image_id' => 'integer',
    'iaas_compute_pool_id' => 'integer',
    'auto_backup_interval' => 'string',
    'auto_backup_time' => 'string',
    'snapshot_of_virtual_machine' => 'integer',
    'backup_repository_id' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_metadata_request',
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
        parent::observe(VirtualMachinesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_machines');

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

    public function gateways() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\Gateways::class);
    }

    public function dhcpServers() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\DhcpServers::class);
    }

    public function virtualMachineMetrics() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\VirtualMachineMetrics::class);
    }

    public function repositoryImages() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\RepositoryImages::class);
    }

    public function cloudNodes() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\CloudNodes::class);
    }
    
    public function computeMembers() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\ComputeMembers::class);
    }
    
    public function computePools() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\ComputePools::class);
    }
    
    public function virtualNetworkCards() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\VirtualNetworkCards::class);
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
