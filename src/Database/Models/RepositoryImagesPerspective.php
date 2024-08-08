<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\RepositoryImagesPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * RepositoryImagesPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $image
 * @property string $os
 * @property string $distro
 * @property string $version
 * @property boolean $is_latest
 * @property array $supported_virtualizations
 * @property boolean $is_iso
 * @property boolean $is_public
 * @property boolean $is_virtual_machine_image
 * @property boolean $is_docker_image
 * @property integer $iaas_repository_id
 * @property string $repository_name
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class RepositoryImagesPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_repository_images_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'image',
            'os',
            'distro',
            'version',
            'is_latest',
            'supported_virtualizations',
            'is_iso',
            'is_public',
            'is_virtual_machine_image',
            'is_docker_image',
            'iaas_repository_id',
            'repository_name',
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
    'image' => 'string',
    'os' => 'string',
    'distro' => 'string',
    'version' => 'string',
    'is_latest' => 'boolean',
    'supported_virtualizations' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'is_iso' => 'boolean',
    'is_public' => 'boolean',
    'is_virtual_machine_image' => 'boolean',
    'is_docker_image' => 'boolean',
    'iaas_repository_id' => 'integer',
    'repository_name' => 'string',
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
        parent::observe(RepositoryImagesPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_repository_images_perspective');

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
