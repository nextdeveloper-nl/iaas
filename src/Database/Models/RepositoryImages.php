<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\RepositoryImagesObserver;

/**
 * RepositoryImages model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $path
 * @property string $filename
 * @property string $default_username
 * @property string $default_password
 * @property boolean $is_active
 * @property boolean $is_iso
 * @property boolean $is_virtual_machine_image
 * @property boolean $is_docker_image
 * @property string $os
 * @property string $distro
 * @property string $version
 * @property string $release_version
 * @property boolean $is_latest
 * @property string $extra
 * @property string $cpu_type
 * @property array $supported_virtualizations
 * @property integer $iaas_repository_id
 * @property string $hash
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property integer $size
 * @property integer $ram
 * @property integer $cpu
 * @property boolean $is_public
 * @property integer $iaas_virtual_machine_id
 */
class RepositoryImages extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_repository_images';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'path',
            'filename',
            'default_username',
            'default_password',
            'is_active',
            'is_iso',
            'is_virtual_machine_image',
            'is_docker_image',
            'os',
            'distro',
            'version',
            'release_version',
            'is_latest',
            'extra',
            'cpu_type',
            'supported_virtualizations',
            'iaas_repository_id',
            'hash',
            'iam_account_id',
            'iam_user_id',
            'size',
            'ram',
            'cpu',
            'is_public',
            'iaas_virtual_machine_id',
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
    'path' => 'string',
    'filename' => 'string',
    'default_username' => 'string',
    'default_password' => 'string',
    'is_active' => 'boolean',
    'is_iso' => 'boolean',
    'is_virtual_machine_image' => 'boolean',
    'is_docker_image' => 'boolean',
    'os' => 'string',
    'distro' => 'string',
    'version' => 'string',
    'release_version' => 'string',
    'is_latest' => 'boolean',
    'extra' => 'string',
    'cpu_type' => 'string',
    'supported_virtualizations' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'iaas_repository_id' => 'integer',
    'hash' => 'string',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'size' => 'integer',
    'ram' => 'integer',
    'cpu' => 'integer',
    'is_public' => 'boolean',
    'iaas_virtual_machine_id' => 'integer',
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
        parent::observe(RepositoryImagesObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_repository_images');

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

    public function virtualMachines() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\NextDeveloper\IAAS\Database\Models\VirtualMachines::class);
    }

    public function accounts() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Accounts::class);
    }

    public function users() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class);
    }

    public function repositories() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\Repositories::class);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

















}
