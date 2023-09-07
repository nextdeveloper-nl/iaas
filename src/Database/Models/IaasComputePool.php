<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\IaasComputePoolObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;

/**
* Class IaasComputePool.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class IaasComputePool extends Model
{
use Filterable, UuidId;
	use SoftDeletes;


	public $timestamps = true;

protected $table = 'iaas_compute_pools';


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
'id'                      => 'integer',
		'uuid'                    => 'string',
		'name'                    => 'string',
		'resource_validator'      => 'string',
		'virtualization_version'  => 'string',
		'provisioning_alg'        => 'string',
		'management_package_name' => 'string',
		'is_active'               => 'boolean',
		'is_alive'                => 'boolean',
		'is_public'               => 'boolean',
		'iaas_datacenter_id'      => 'integer',
		'iaas_cloud_node_id'      => 'integer',
		'iam_account_id'          => 'integer',
		'iam_user_id'             => 'integer',
		'created_at'              => 'datetime',
		'updated_at'              => 'datetime',
		'deleted_at'              => 'datetime',
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
parent::observe(IaasComputePoolObserver::class);

self::registerScopes();
}

public static function registerScopes()
{
$globalScopes = config('iaas.scopes.global');
$modelScopes = config('iaas.scopes.iaas_compute_pools');

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

public function iaasComputeMembers()
    {
        return $this->hasMany(IaasComputeMember::class);
    }

    public function iaasCloudNode()
    {
        return $this->belongsTo(IaasCloudNode::class);
    }
    
    public function iaasDatacenter()
    {
        return $this->belongsTo(IaasDatacenter::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n
}