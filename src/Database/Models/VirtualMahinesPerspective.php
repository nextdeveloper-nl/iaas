<?php

namespace NextDeveloper\IAAS\Database\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use NextDeveloper\Commons\Database\Traits\Filterable;
use NextDeveloper\IAAS\Database\Observers\VirtualMahinesPerspectiveObserver;
use NextDeveloper\Commons\Database\Traits\UuidId;
use NextDeveloper\Commons\Common\Cache\Traits\CleanCache;
use NextDeveloper\Commons\Database\Traits\Taggable;

/**
 * VirtualMahinesPerspective model.
 *
 * @package  NextDeveloper\IAAS\Database\Models
 * @property integer $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $hostname
 * @property string $username
 * @property string $os
 * @property string $distro
 * @property string $version
 * @property string $domain_type
 * @property string $status
 * @property integer $cpu
 * @property integer $ram
 * @property \Carbon\Carbon $last_metadata_request
 * @property integer $iaas_cloud_node_id
 * @property string $cloud_node
 * @property integer $common_domain_id
 * @property string $domain
 * @property integer $disk_count
 * @property integer $network_card_count
 * @property array $tags
 * @property boolean $is_template
 * @property boolean $is_draft
 * @property string $maintainer
 * @property string $responsible
 * @property integer $iam_account_id
 * @property integer $iam_user_id
 */
class VirtualMahinesPerspective extends Model
{
    use Filterable, UuidId, CleanCache, Taggable;


    public $timestamps = false;

    protected $table = 'iaas_virtual_mahines_perspective';


    /**
     @var array
     */
    protected $guarded = [];

    protected $fillable = [
            'name',
            'description',
            'hostname',
            'username',
            'os',
            'distro',
            'version',
            'domain_type',
            'status',
            'cpu',
            'ram',
            'last_metadata_request',
            'iaas_cloud_node_id',
            'cloud_node',
            'common_domain_id',
            'domain',
            'disk_count',
            'network_card_count',
            'tags',
            'is_template',
            'is_draft',
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
    'name' => 'string',
    'description' => 'string',
    'hostname' => 'string',
    'username' => 'string',
    'os' => 'string',
    'distro' => 'string',
    'version' => 'string',
    'domain_type' => 'string',
    'status' => 'string',
    'cpu' => 'integer',
    'ram' => 'integer',
    'last_metadata_request' => 'datetime',
    'iaas_cloud_node_id' => 'integer',
    'cloud_node' => 'string',
    'common_domain_id' => 'integer',
    'domain' => 'string',
    'disk_count' => 'integer',
    'network_card_count' => 'integer',
    'tags' => \NextDeveloper\Commons\Database\Casts\TextArray::class,
    'is_template' => 'boolean',
    'is_draft' => 'boolean',
    'maintainer' => 'string',
    'responsible' => 'string',
    ];

    /**
     We are casting data fields.
     *
     @var array
     */
    protected $dates = [
    'last_metadata_request',
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
        parent::observe(VirtualMahinesPerspectiveObserver::class);

        self::registerScopes();
    }

    public static function registerScopes()
    {
        $globalScopes = config('iaas.scopes.global');
        $modelScopes = config('iaas.scopes.iaas_virtual_mahines_perspective');

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
