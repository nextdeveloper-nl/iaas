<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\Commons\Database\Traits\HasStates;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\IAAS\Database\Observers\VirtualMachineBackupsObserver;
use Illuminate\Notifications\Notifiable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;
use NextDeveloper\Commons\Database\Traits\HasObject;

/**
 * VirtualMachineBackups model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $path
 * @property string $filename
 * @property string $username
 * @property string $password
 * @property integer $size
 * @property integer $ram
 * @property integer $cpu
 * @property string $hash
 * @property string $backup_type
 * @property integer $iaas_virtual_machine_id
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property string $status
 * @property \Carbon\Carbon $backup_starts
 * @property \Carbon\Carbon $backup_ends
 * @property integer $iaas_repository_image_id
 * @property integer $iaas_backup_job_id
 * @property $data
 * @property integer $progress
 */
class VirtualMachineBackups extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_virtual_machine_backups';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'path',
            'filename',
            'username',
            'password',
            'size',
            'ram',
            'cpu',
            'hash',
            'backup_type',
            'iaas_virtual_machine_id',
            'iam_account_id',
            'iam_user_id',
            'status',
            'backup_starts',
            'backup_ends',
            'iaas_repository_image_id',
            'iaas_backup_job_id',
            'data',
            'progress',
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
    'username' => 'string',
    'password' => 'string',
    'size' => 'integer',
    'ram' => 'integer',
    'cpu' => 'integer',
    'hash' => 'string',
    'backup_type' => 'string',
    'iaas_virtual_machine_id' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'status' => 'string',
    'backup_starts' => 'datetime',
    'backup_ends' => 'datetime',
    'iaas_repository_image_id' => 'integer',
    'iaas_backup_job_id' => 'integer',
    'data' => 'array',
    'progress' => 'integer',
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
    'backup_starts',
    'backup_ends',
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
        parent::observe(VirtualMachineBackupsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_machine_backups');

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

    protected function password(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: function ($value) {
                return encrypt($value);
            },
        );
    }






}
