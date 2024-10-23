<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\AnsibleSystemPlaybookExecutionsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * AnsibleSystemPlaybookExecutions model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property integer $iaas_ansible_system_plays_id
 * @property \Carbon\Carbon $last_execution_time
 * @property string $package
 * @property $config
 * @property integer $execution_total_time
 * @property string $last_output
 * @property integer $result_ok
 * @property integer $result_unreachable
 * @property integer $result_failed
 * @property integer $result_skipped
 * @property integer $result_rescued
 * @property integer $result_ignored
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property integer $iaas_ansible_system_playbook_id
 */
class AnsibleSystemPlaybookExecutions extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'iaas_ansible_system_playbook_executions';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'iaas_ansible_system_plays_id',
            'last_execution_time',
            'package',
            'config',
            'execution_total_time',
            'last_output',
            'result_ok',
            'result_unreachable',
            'result_failed',
            'result_skipped',
            'result_rescued',
            'result_ignored',
            'iam_account_id',
            'iam_user_id',
            'iaas_ansible_system_playbook_id',
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
    'iaas_ansible_system_plays_id' => 'integer',
    'last_execution_time' => 'datetime',
    'package' => 'string',
    'config' => 'array',
    'execution_total_time' => 'integer',
    'last_output' => 'string',
    'result_ok' => 'integer',
    'result_unreachable' => 'integer',
    'result_failed' => 'integer',
    'result_skipped' => 'integer',
    'result_rescued' => 'integer',
    'result_ignored' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'iaas_ansible_system_playbook_id' => 'integer',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_execution_time',
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
        parent::observe(AnsibleSystemPlaybookExecutionsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_ansible_system_playbook_executions');

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
    
    public function users() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAM\Database\Models\Users::class);
    }
    
    public function ansibleSystemPlaybooks() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybooks::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

































}
