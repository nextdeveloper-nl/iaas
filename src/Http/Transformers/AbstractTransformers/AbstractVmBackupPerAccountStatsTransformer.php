<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\Commons\Database\Models\Addresses;
use NextDeveloper\Commons\Database\Models\Comments;
use NextDeveloper\Commons\Database\Models\Meta;
use NextDeveloper\Commons\Database\Models\PhoneNumbers;
use NextDeveloper\Commons\Database\Models\SocialMedia;
use NextDeveloper\Commons\Database\Models\Votes;
use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Http\Transformers\MediaTransformer;
use NextDeveloper\Commons\Database\Models\AvailableActions;
use NextDeveloper\Commons\Http\Transformers\AvailableActionsTransformer;
use NextDeveloper\Commons\Database\Models\States;
use NextDeveloper\Commons\Http\Transformers\StatesTransformer;
use NextDeveloper\Commons\Http\Transformers\CommentsTransformer;
use NextDeveloper\Commons\Http\Transformers\SocialMediaTransformer;
use NextDeveloper\Commons\Http\Transformers\MetaTransformer;
use NextDeveloper\Commons\Http\Transformers\VotesTransformer;
use NextDeveloper\Commons\Http\Transformers\AddressesTransformer;
use NextDeveloper\Commons\Http\Transformers\PhoneNumbersTransformer;
use NextDeveloper\IAAS\Database\Models\VmBackupPerAccountStats;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class VmBackupPerAccountStatsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVmBackupPerAccountStatsTransformer extends AbstractTransformer
{

    /**
     * @var array
     */
    protected array $availableIncludes = [
        'states',
        'actions',
        'media',
        'comments',
        'votes',
        'socialMedia',
        'phoneNumbers',
        'addresses',
        'meta'
    ];

    /**
     * @param VmBackupPerAccountStats $model
     *
     * @return array
     */
    public function transform(VmBackupPerAccountStats $model)
    {
                                                $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                        
        return $this->buildPayload(
            [
            'id'  =>  $model->id,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'vms_protected'  =>  $model->vms_protected,
            'vms_protected_delta'  =>  $model->vms_protected_delta,
            'vms_protected_delta_pct'  =>  $model->vms_protected_delta_pct,
            'rpo_breached_vms'  =>  $model->rpo_breached_vms,
            'sla_breached_jobs'  =>  $model->sla_breached_jobs,
            'jobs_disabled'  =>  $model->jobs_disabled,
            'jobs_failed_24h'  =>  $model->jobs_failed_24h,
            'jobs_failed_30d'  =>  $model->jobs_failed_30d,
            'avg_rpo_achieved_hours'  =>  $model->avg_rpo_achieved_hours,
            'avg_rpo_target_hours'  =>  $model->avg_rpo_target_hours,
            'storage_used_bytes'  =>  $model->storage_used_bytes,
            'storage_used_gb'  =>  $model->storage_used_gb,
            'storage_used_tb'  =>  $model->storage_used_tb,
            'protections_done_24h'  =>  $model->protections_done_24h,
            'protections_done_30d'  =>  $model->protections_done_30d,
            'protections_done_delta'  =>  $model->protections_done_delta,
            'protections_done_delta_pct'  =>  $model->protections_done_delta_pct,
            'jobs_with_replication'  =>  $model->jobs_with_replication,
            ]
        );
    }

    public function includeStates(VmBackupPerAccountStats $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(VmBackupPerAccountStats $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(VmBackupPerAccountStats $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(VmBackupPerAccountStats $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(VmBackupPerAccountStats $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(VmBackupPerAccountStats $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(VmBackupPerAccountStats $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(VmBackupPerAccountStats $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(VmBackupPerAccountStats $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
