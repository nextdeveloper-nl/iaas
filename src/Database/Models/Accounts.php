<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\AccountsObserver;

/**
 * Accounts model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property $limits
 * @property integer $iam_account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property boolean $is_service_enabled
 * @property boolean $is_suspended
 */
class Accounts extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_accounts';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'limits',
            'iam_account_id',
            'is_service_enabled',
            'is_suspended',
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
    'limits' => 'array',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'is_service_enabled' => 'boolean',
    'is_suspended' => 'boolean',
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
        parent::observe(AccountsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_accounts');

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

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE






}
