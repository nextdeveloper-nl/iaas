<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VirtualMachinesPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * VirtualMachinesPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $hostname
 * @property string $username
 * @property string $os
 * @property string $distro
 * @property string $version
 * @property string $domain_type
 * @property string $status
 * @property integer $cpu
 * @property integer $ram
 * @property \Carbon\Carbon $last_metadata_request
 * @property integer $iaas_cloud_node_id
 * @property string $cloud_node
 * @property integer $common_domain_id
 * @property string $domain
 * @property integer $disk_count
 * @property integer $network_card_count
 * @property integer $has_warnings
 * @property integer $has_errors
 * @property integer $number_of_disks
 * @property integer $total_disk_size
 * @property string $network
 * @property $ip_addr
 * @property $states
 * @property array $tags
 * @property boolean $is_template
 * @property boolean $is_draft
 * @property boolean $is_lost
 * @property boolean $is_locked
 * @property boolean $is_snapshot
 * @property string $auto_backup_interval
 * @property string $auto_backup_time
 * @property string $maintainer
 * @property string $responsible
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class VirtualMachinesPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_virtual_machines_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'hostname',
            'username',
            'os',
            'distro',
            'version',
            'domain_type',
            'status',
            'cpu',
            'ram',
            'last_metadata_request',
            'iaas_cloud_node_id',
            'cloud_node',
            'common_domain_id',
            'domain',
            'disk_count',
            'network_card_count',
            'has_warnings',
            'has_errors',
            'number_of_disks',
            'total_disk_size',
            'network',
            'ip_addr',
            'states',
            'tags',
            'is_template',
            'is_draft',
            'is_lost',
            'is_locked',
            'is_snapshot',
            'auto_backup_interval',
            'auto_backup_time',
            'maintainer',
            'responsible',
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
    'description' => 'string',
    'hostname' => 'string',
    'username' => 'string',
    'os' => 'string',
    'distro' => 'string',
    'version' => 'string',
    'domain_type' => 'string',
    'status' => 'string',
    'cpu' => 'integer',
    'ram' => 'integer',
    'last_metadata_request' => 'datetime',
    'iaas_cloud_node_id' => 'integer',
    'cloud_node' => 'string',
    'common_domain_id' => 'integer',
    'domain' => 'string',
    'disk_count' => 'integer',
    'network_card_count' => 'integer',
    'has_warnings' => 'integer',
    'has_errors' => 'integer',
    'number_of_disks' => 'integer',
    'total_disk_size' => 'integer',
    'network' => 'string',
    'states' => 'array',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'is_template' => 'boolean',
    'is_draft' => 'boolean',
    'is_lost' => 'boolean',
    'is_locked' => 'boolean',
    'is_snapshot' => 'boolean',
    'auto_backup_interval' => 'string',
    'auto_backup_time' => 'string',
    'maintainer' => 'string',
    'responsible' => 'string',
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
        parent::observe(VirtualMachinesPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_machines_perspective');

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
