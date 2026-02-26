<?php

namespace NextDeveloper\IAAS\Database\Models;

use NextDeveloper\Commons\Database\Traits\HasStates;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VirtualMachineBackupsPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Database\Traits\HasObject;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\RunAsAdministrator;

/**
 * VirtualMachineBackupsPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $path
 * @property string $filename
 * @property integer $cpu
 * @property integer $ram
 * @property string $backup_type
 * @property integer $iaas_virtual_machine_id
 * @property integer $iam_user_id
 * @property integer $iam_account_id
 * @property string $status
 * @property \Carbon\Carbon $backup_starts
 * @property \Carbon\Carbon $backup_ends
 * @property integer $iaas_repository_image_id
 * @property integer $iaas_repository_id
 * @property integer $iaas_backup_job_id
 * @property integer $progress
 * @property string $hash
 * @property boolean $is_latest
 * @property integer $size
 * @property string $os
 * @property string $distro
 * @property string $cpu_type
 * @property array $supported_virtualizations
 * @property string $hostname
 */
class VirtualMachineBackupsPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates, RunAsAdministrator, HasObject;

    public $timestamps = false;

    protected $table = 'iaas_virtual_machine_backups_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'path',
            'filename',
            'cpu',
            'ram',
            'backup_type',
            'iaas_virtual_machine_id',
            'iam_user_id',
            'iam_account_id',
            'status',
            'backup_starts',
            'backup_ends',
            'iaas_repository_image_id',
            'iaas_repository_id',
            'iaas_backup_job_id',
            'progress',
            'hash',
            'is_latest',
            'size',
            'os',
            'distro',
            'cpu_type',
            'supported_virtualizations',
            'hostname',
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
    'cpu' => 'integer',
    'ram' => 'integer',
    'backup_type' => 'string',
    'iaas_virtual_machine_id' => 'integer',
    'status' => 'string',
    'backup_starts' => 'datetime',
    'backup_ends' => 'datetime',
    'iaas_repository_image_id' => 'integer',
    'iaas_repository_id' => 'integer',
    'iaas_backup_job_id' => 'integer',
    'progress' => 'integer',
    'hash' => 'string',
    'is_latest' => 'boolean',
    'size' => 'integer',
    'os' => 'string',
    'distro' => 'string',
    'cpu_type' => 'string',
    'supported_virtualizations' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'hostname' => 'string',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
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
        parent::observe(VirtualMachineBackupsPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_machine_backups_perspective');

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
