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
use NextDeveloper\IAAS\Database\Models\VirtualMachines;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class VirtualMachinesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVirtualMachinesTransformer extends AbstractTransformer
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
     * @param VirtualMachines $model
     *
     * @return array
     */
    public function transform(VirtualMachines $model)
    {
        $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('id', $model->iaas_cloud_node_id)->first();
        $iaasComputeMemberId = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('id', $model->iaas_compute_member_id)->first();
        $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
        $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        $templateId = \NextDeveloper\IAAS\Database\Models\VirtualMachines::where('id', $model->template_id)->first();
        $commonDomainId = \NextDeveloper\Commons\Database\Models\Domains::where('id', $model->common_domain_id)->first();
        $iaasRepositoryImageId = \NextDeveloper\IAAS\Database\Models\RepositoryImages::where('id', $model->iaas_repository_image_id)->first();
        $iaasComputePoolId = \NextDeveloper\IAAS\Database\Models\ComputePools::where('id', $model->iaas_compute_pool_id)->first();

        return $this->buildPayload(
            [
                'id' => $model->uuid,
                'name' => $model->name,
                'username' => $model->username,
                'password' => $model->password,
                'hostname' => $model->hostname,
                'description' => $model->description,
                'os' => $model->os,
                'distro' => $model->distro,
                'version' => $model->version,
                'domain_type' => $model->domain_type,
                'status' => $model->status,
                'cpu' => $model->cpu,
                'ram' => $model->ram,
                'is_winrm_enabled' => $model->is_winrm_enabled,
                'available_operations' => $model->available_operations,
                'current_operations' => $model->current_operations,
                'blocked_operations' => $model->blocked_operations,
                'console_data' => $model->console_data,
                'is_snapshot' => $model->is_snapshot,
                'is_lost' => $model->is_lost,
                'is_locked' => $model->is_locked,
                'last_metadata_request' => $model->last_metadata_request,
                'features' => $model->features,
                'hypervisor_uuid' => $model->hypervisor_uuid,
                'hypervisor_data' => $model->hypervisor_data,
                'iaas_cloud_node_id' => $iaasCloudNodeId ? $iaasCloudNodeId->uuid : null,
                'iaas_compute_member_id' => $iaasComputeMemberId ? $iaasComputeMemberId->uuid : null,
                'iam_account_id' => $iamAccountId ? $iamAccountId->uuid : null,
                'iam_user_id' => $iamUserId ? $iamUserId->uuid : null,
                'template_id' => $templateId ? $templateId->uuid : null,
                'tags' => $model->tags,
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
                'deleted_at' => $model->deleted_at,
                'is_draft' => $model->is_draft,
                'common_domain_id' => $commonDomainId ? $commonDomainId->uuid : null,
                'lock_password' => $model->lock_password,
                'is_template' => $model->is_template,
                'iaas_repository_image_id' => $iaasRepositoryImageId ? $iaasRepositoryImageId->uuid : null,
                'iaas_compute_pool_id' => $iaasComputePoolId ? $iaasComputePoolId->uuid : null,
                'auto_backup_interval' => $model->auto_backup_interval,
                'auto_backup_time' => $model->auto_backup_time,
                'snapshot_of_virtual_machine'   =>  $model->snapshot_of_virtual_machine
            ]
        );
    }

    public function includeStates(VirtualMachines $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(VirtualMachines $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(VirtualMachines $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(VirtualMachines $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(VirtualMachines $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(VirtualMachines $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(VirtualMachines $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(VirtualMachines $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(VirtualMachines $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}
