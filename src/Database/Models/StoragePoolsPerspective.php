<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\StoragePoolsPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * StoragePoolsPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property $name
 * @property $price_pergb
 * @property boolean $is_active
 * @property string $currency
 * @property string $datacenter
 * @property string $cloud_node
 * @property array $tags
 * @property string $maintainer
 * @property string $responsible
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 */
class StoragePoolsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable;


    public $timestamps = false;

    protected $table = 'iaas_storage_pools_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'price_pergb',
            'is_active',
            'currency',
            'datacenter',
            'cloud_node',
            'tags',
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
    'is_active' => 'boolean',
    'currency' => 'string',
    'datacenter' => 'string',
    'cloud_node' => 'string',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'maintainer' => 'string',
    'responsible' => 'string',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [

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
        parent::observe(StoragePoolsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_storage_pools_perspective');

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
