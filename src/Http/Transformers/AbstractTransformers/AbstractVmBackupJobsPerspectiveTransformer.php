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
use NextDeveloper\IAAS\Database\Models\VmBackupJobsPerspective;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class VmBackupJobsPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVmBackupJobsPerspectiveTransformer extends AbstractTransformer
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
     * @param VmBackupJobsPerspective $model
     *
     * @return array
     */
    public function transform(VmBackupJobsPerspective $model)
    {
                                                $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                                                            $iaasVirtualMachineId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->iaas_virtual_machine_id)->first();
                        
        return $this->buildPayload(
            [
            'id'  =>  $model->id,
            'job_name'  =>  $model->job_name,
            'job_type'  =>  $model->job_type,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iaas_virtual_machine_id'  =>  $iaasVirtualMachineId ? $iaasVirtualMachineId->uuid : null,
            'is_enabled'  =>  $model->is_enabled,
            'expected_rpo_hours'  =>  $model->expected_rpo_hours,
            'expected_rto_hours'  =>  $model->expected_rto_hours,
            'max_allowed_failures'  =>  $model->max_allowed_failures,
            'sla_target_pct'  =>  $model->sla_target_pct,
            'notification_webhook'  =>  $model->notification_webhook,
            'email_notification_recipients'  =>  $model->email_notification_recipients,
            'virtual_machine_name'  =>  $model->virtual_machine_name,
            'hostname'  =>  $model->hostname,
            'retention_policy_name'  =>  $model->retention_policy_name,
            'keep_for_days'  =>  $model->keep_for_days,
            'keep_last_n_backups'  =>  $model->keep_last_n_backups,
            'is_scheduled'  =>  $model->is_scheduled,
            'last_run_at'  =>  $model->last_run_at,
            'last_run_ended_at'  =>  $model->last_run_ended_at,
            'last_run_status'  =>  $model->last_run_status,
            'last_run_progress'  =>  $model->last_run_progress,
            'last_run_duration_secs'  =>  $model->last_run_duration_secs,
            'last_run_size_bytes'  =>  $model->last_run_size_bytes,
            'consecutive_failures'  =>  $model->consecutive_failures,
            'rpo_breached'  =>  $model->rpo_breached,
            'rpo_achieved_hours'  =>  $model->rpo_achieved_hours,
            'sla_breached'  =>  $model->sla_breached,
            'status_indicator'  =>  $model->status_indicator,
            'replication_count'  =>  $model->replication_count,
            'replication_ok_count'  =>  $model->replication_ok_count,
            'replication_failed_count'  =>  $model->replication_failed_count,
            'last_replication_at'  =>  $model->last_replication_at,
            'replication_status_indicator'  =>  $model->replication_status_indicator,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            ]
        );
    }

    public function includeStates(VmBackupJobsPerspective $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(VmBackupJobsPerspective $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(VmBackupJobsPerspective $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(VmBackupJobsPerspective $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(VmBackupJobsPerspective $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(VmBackupJobsPerspective $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(VmBackupJobsPerspective $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(VmBackupJobsPerspective $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(VmBackupJobsPerspective $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
