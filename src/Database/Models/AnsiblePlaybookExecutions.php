<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\AnsiblePlaybookExecutionsObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;
use NextDeveloper\Commons\Database\Traits\SSHable;
use NextDeveloper\IAAS\Database\Traits\Agentable;
use NextDeveloper\Commons\Database\Traits\HasStates;

/**
 * AnsiblePlaybookExecutions model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property boolean $is_external_machine
 * @property integer $iaas_virtual_machine_id
 * @property string $ssh_username
 * @property string $ssh_password
 * @property integer $ssh_port
 * @property $ip_v4
 * @property $ip_v6
 * @property \Carbon\Carbon $last_execution_time
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
 * @property integer $iaas_ansible_playbook_id
 */
class AnsiblePlaybookExecutions extends Model
{
    use Filterable, UuidId, CleanCache, Taggable, HasStates;
    use SoftDeletes;
    use SSHable, Agentable;

    public $timestamps = true;

    protected $table = 'iaas_ansible_playbook_executions';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'is_external_machine',
            'iaas_virtual_machine_id',
            'ssh_username',
            'ssh_password',
            'ssh_port',
            'ip_v4',
            'ip_v6',
            'last_execution_time',
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
            'iaas_ansible_playbook_id',
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
    'is_external_machine' => 'boolean',
    'iaas_virtual_machine_id' => 'integer',
    'ssh_username' => 'string',
    'ssh_password' => 'string',
    'ssh_port' => 'integer',
    'last_execution_time' => 'datetime',
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
    'iaas_ansible_playbook_id' => 'integer',
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
        parent::observe(AnsiblePlaybookExecutionsObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_ansible_playbook_executions');

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
    
    public function ansiblePlaybooks() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\NextDeveloper\IAAS\Database\Models\AnsiblePlaybooks::class);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE





































}
