<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\IaasDatacenterObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;

/**
* Class IaasDatacenter.
*
* @package NextDeveloper\IAAS\Database\Models
*/
class IaasDatacenter extends Model
{
use Filterable, UuidId;
	use SoftDeletes;


	public $timestamps = true;

protected $table = 'iaas_datacenters';


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
'id'                 => 'integer',
		'uuid'               => 'string',
		'name'               => 'string',
		'slug'               => 'string',
		'is_public'          => 'boolean',
		'is_active'          => 'boolean',
		'maintenance_mode'   => 'boolean',
		'geo_latitude'       => 'string',
		'geo_longitude'      => 'string',
		'total_capacity'     => 'integer',
		'guaranteed_uptime'  => 'double',
		'is_carrier_neutral' => 'boolean',
		'city'               => 'string',
		'iam_account_id'     => 'integer',
		'common_country_id'  => 'integer',
		'created_at'         => 'datetime',
		'updated_at'         => 'datetime',
		'deleted_at'         => 'datetime',
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
parent::observe(IaasDatacenterObserver::class);

self::registerScopes();
}

public static function registerScopes()
{
$globalScopes = config('iaas.scopes.global');
$modelScopes = config('iaas.scopes.iaas_datacenters');

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

public function iaasCloudNodes()
    {
        return $this->hasMany(IaasCloudNode::class);
    }

    public function iaasComputePools()
    {
        return $this->hasMany(IaasComputePool::class);
    }

    public function iaasNetworkPools()
    {
        return $this->hasMany(IaasNetworkPool::class);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n\n\n
}