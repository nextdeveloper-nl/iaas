<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\NetworkMembersInterfacesObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * NetworkMembersInterfaces model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property $ip_addr
 * @property string $configuration
 * @property integer $iaas_network_member_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property boolean $is_up
 * @property integer $iaas_network_id
 * @property boolean $is_shutdown
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 */
class NetworkMembersInterfaces extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_network_members_interfaces';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'ip_addr',
            'configuration',
            'iaas_network_member_id',
            'is_up',
            'iaas_network_id',
            'is_shutdown',
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
    'configuration' => 'string',
    'iaas_network_member_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_up' => 'boolean',
    'iaas_network_id' => 'integer',
    'is_shutdown' => 'boolean',
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
        parent::observe(NetworkMembersInterfacesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_network_members_interfaces');

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

    public function networkMembers() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\NetworkMembers::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











}
