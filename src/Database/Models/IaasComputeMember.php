<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\IaasComputeMemberObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;

/**
* Class IaasComputeMember.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class IaasComputeMember extends Model
{
use Filterable, UuidId;
	use SoftDeletes;


	public $timestamps = true;

protected $table = 'iaas_compute_members';


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
'id'                    => 'integer',
		'uuid'                  => 'string',
		'name'                  => 'string',
		'hostname'              => 'string',
		'ip_addr'               => 'string',
		'local_ip_addr'         => 'string',
		'api_url'               => 'string',
		'port'                  => 'integer',
		'username'              => 'string',
		'password'              => 'string',
		'features'              => 'string',
		'is_behind_firewall'    => 'boolean',
		'hypervisor_uuid'       => 'string',
		'hypervisor_data'       => 'string',
		'total_cpu'             => 'integer',
		'total_ram'             => 'integer',
		'used_cpu'              => 'integer',
		'used_ram'              => 'integer',
		'free_cpu'              => 'integer',
		'free_ram'              => 'integer',
		'total_vm'              => 'integer',
		'overbooking_ratio'     => 'double',
		'max_overbooking_ratio' => 'integer',
		'uptime'                => 'integer',
		'idle_time'             => 'integer',
		'benchmark_score'       => 'integer',
		'is_maintenance'        => 'boolean',
		'is_alive'              => 'boolean',
		'iaas_compute_pool_id'  => 'integer',
		'iam_account_id'        => 'integer',
		'iam_user_id'           => 'integer',
		'created_at'            => 'datetime',
		'updated_at'            => 'datetime',
		'deleted_at'            => 'datetime',
];

/**
* We are casting data fields.
* @var array
*/
protected $dates = [
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
parent::observe(IaasComputeMemberObserver::class);

self::registerScopes();
}

public static function registerScopes()
{
$globalScopes = config('iaas.scopes.global');
$modelScopes = config('iaas.scopes.iaas_compute_members');

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

public function iaasComputePool()
    {
        return $this->belongsTo(IaasComputePool::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n
}