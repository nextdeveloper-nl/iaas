<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VirtualMachinesObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;

/**
* Class VirtualMachines.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class VirtualMachines extends Model
{
use Filterable, UuidId;
	use SoftDeletes;


	public $timestamps = true;

protected $table = 'iaas_virtual_machines';


/**
* @var array
*/
protected $guarded = [];

/**
*  Here we have the fulltext fields. We can use these for fulltext search if enabled.
*/
protected $fullTextFields = [

];

/**
* @var array
*/
protected $appends = [

];

/**
* We are casting fields to objects so that we can work on them better
* @var array
*/
protected $casts = [
'id'                     => 'integer',
		'uuid'                   => 'string',
		'name'                   => 'string',
		'username'               => 'string',
		'password'               => 'string',
		'hostname'               => 'string',
		'description'            => 'string',
		'notes'                  => 'string',
		'os'                     => 'string',
		'distro'                 => 'string',
		'version'                => 'string',
		'cpu'                    => 'boolean',
		'ram'                    => 'integer',
		'winrm_enabled'          => 'boolean',
		'is_snapshot'            => 'boolean',
		'is_lost'                => 'boolean',
		'is_locked'              => 'boolean',
		'last_metadata_request'  => 'datetime',
		'features'               => 'string',
		'hypervisor_uuid'        => 'string',
		'hypervisor_data'        => 'string',
		'iaas_cloud_node_id'     => 'integer',
		'iaas_compute_member_id' => 'integer',
		'iam_account_id'         => 'integer',
		'iam_user_id'            => 'integer',
		'from_template_id'       => 'integer',
		'suspended_at'           => 'datetime',
		'created_at'             => 'datetime',
		'updated_at'             => 'datetime',
		'deleted_at'             => 'datetime',
];

/**
* We are casting data fields.
* @var array
*/
protected $dates = [
'last_metadata_request',
		'suspended_at',
		'created_at',
		'updated_at',
		'deleted_at',
];

/**
* @var array
*/
protected $with = [

];

/**
* @var int
*/
protected $perPage = 20;

/**
* @return void
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

if(!$modelScopes) $modelScopes = [];
if (!$globalScopes) $globalScopes = [];

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

public function cloudNodes()
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\CloudNodes::class);
    }
    
    public function computeMembers()
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\ComputeMembers::class);
    }
    
    public function accounts()
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Accounts::class);
    }
    
    public function users()
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n
}