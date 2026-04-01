<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\CustomerResourcesPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * CustomerResourcesPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $iam_account_id
 * @property string $account_uuid
 * @property string $account_name
 * @property string $user_name
 * @property string $user_email
 * @property boolean $is_account_suspended
 * @property string $crm_account_uuid
 * @property boolean $is_crm_suspended
 * @property boolean $is_crm_disabled
 * @property boolean $is_accounting_disabled
 * @property string $resource_type
 * @property integer $resource_id
 * @property string $resource_uuid
 * @property string $resource_name
 * @property string $resource_status
 * @property integer $cpu
 * @property integer $ram
 * @property integer $iaas_cloud_node_id
 * @property string $hypervisor_name_label
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $deleted_at
 */
class CustomerResourcesPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = false;

    protected $table = 'iaas_customer_resources_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iam_account_id',
            'account_uuid',
            'account_name',
            'user_name',
            'user_email',
            'is_account_suspended',
            'crm_account_uuid',
            'is_crm_suspended',
            'is_crm_disabled',
            'is_accounting_disabled',
            'resource_type',
            'resource_id',
            'resource_uuid',
            'resource_name',
            'resource_status',
            'cpu',
            'ram',
            'iaas_cloud_node_id',
            'hypervisor_name_label',
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
    'account_name' => 'string',
    'user_name' => 'string',
    'user_email' => 'string',
    'is_account_suspended' => 'boolean',
    'is_crm_suspended' => 'boolean',
    'is_crm_disabled' => 'boolean',
    'is_accounting_disabled' => 'boolean',
    'resource_type' => 'string',
    'resource_id' => 'integer',
    'resource_name' => 'string',
    'resource_status' => 'string',
    'cpu' => 'integer',
    'ram' => 'integer',
    'iaas_cloud_node_id' => 'integer',
    'hypervisor_name_label' => 'string',
    'created_at' => 'datetime',
    'deleted_at' => 'datetime',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'created_at',
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
        parent::observe(CustomerResourcesPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_customer_resources_perspective');

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
