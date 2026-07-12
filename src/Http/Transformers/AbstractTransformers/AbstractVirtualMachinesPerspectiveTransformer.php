<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\Commons\Database\Models\Addresses;
use NextDeveloper\Commons\Database\Models\AvailableActions;
use NextDeveloper\Commons\Database\Models\Comments;
use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Database\Models\Meta;
use NextDeveloper\Commons\Database\Models\PhoneNumbers;
use NextDeveloper\Commons\Database\Models\SocialMedia;
use NextDeveloper\Commons\Database\Models\States;
use NextDeveloper\Commons\Database\Models\Votes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\Commons\Http\Transformers\AddressesTransformer;
use NextDeveloper\Commons\Http\Transformers\AvailableActionsTransformer;
use NextDeveloper\Commons\Http\Transformers\CommentsTransformer;
use NextDeveloper\Commons\Http\Transformers\MediaTransformer;
use NextDeveloper\Commons\Http\Transformers\MetaTransformer;
use NextDeveloper\Commons\Http\Transformers\PhoneNumbersTransformer;
use NextDeveloper\Commons\Http\Transformers\SocialMediaTransformer;
use NextDeveloper\Commons\Http\Transformers\StatesTransformer;
use NextDeveloper\Commons\Http\Transformers\VotesTransformer;
use NextDeveloper\IAAS\Database\Models\VirtualMachinesPerspective;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class VirtualMachinesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractVirtualMachinesPerspectiveTransformer extends AbstractTransformer
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
     * @param VirtualMachinesPerspective $model
     *
     * @return array
     */
    public function transform(VirtualMachinesPerspective $model)
    {
        //  iaas_virtual_machines_perspective is a VIEW that already LEFT JOINs cloud_nodes/
        //  domains/compute_members/compute_pools/accounts/users to build cloud_node/domain/
        //  compute_member_name/pool_type/maintainer/responsible - it now also projects each of
        //  their uuid columns directly (cloud_node_uuid, domain_uuid, etc.), so reading them off
        //  the model is free. This used to run 6 extra `where('id', ...)->first()` queries per
        //  row here - on a page of ~60 VMs that was 360 extra round trips for one response.
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'description'  =>  $model->description,
            'hostname'  =>  $model->hostname,
            'username'  =>  $model->username,
            'os'  =>  $model->os,
            'distro'  =>  $model->distro,
            'version'  =>  $model->version,
            'domain_type'  =>  $model->domain_type,
            'status'  =>  $model->status,
            'cpu'  =>  $model->cpu,
            'ram'  =>  $model->ram,
            'last_metadata_request'  =>  $model->last_metadata_request,
            'iaas_cloud_node_id'  =>  $model->cloud_node_uuid,
            'cloud_node'  =>  $model->cloud_node,
            'common_domain_id'  =>  $model->domain_uuid,
            'domain'  =>  $model->domain,
            'disk_count'  =>  $model->disk_count,
            'network_card_count'  =>  $model->network_card_count,
            'has_warnings'  =>  $model->has_warnings,
            'has_errors'  =>  $model->has_errors,
            'number_of_disks'  =>  $model->number_of_disks,
            'total_disk_size'  =>  $model->total_disk_size,
            'network'  =>  $model->network,
            'ip_addr'  =>  $model->ip_addr,
            'states'  =>  $model->states,
            'pool_type'  =>  $model->pool_type,
            'is_snapshot_available'  =>  $model->is_snapshot_available,
            'iaas_compute_member_id'  =>  $model->compute_member_uuid,
            'compute_member_name'  =>  $model->compute_member_name,
            'tags'  =>  $model->tags,
            'is_template'  =>  $model->is_template,
            'is_draft'  =>  $model->is_draft,
            'is_lost'  =>  $model->is_lost,
            'is_locked'  =>  $model->is_locked,
            'is_snapshot'  =>  $model->is_snapshot,
            'auto_backup_interval'  =>  $model->auto_backup_interval,
            'auto_backup_time'  =>  $model->auto_backup_time,
            'post_boot_script'  =>  $model->post_boot_script,
            'agent_latest_ping'  =>  $model->agent_latest_ping,
            'maintainer'  =>  $model->maintainer,
            'responsible'  =>  $model->responsible,
            'iaas_compute_pool_id'  =>  $model->compute_pool_uuid,
            'snapshot_of_virtual_machine'  =>  $model->snapshot_of_virtual_machine,
            'iam_account_id'  =>  $model->account_uuid,
            'iam_user_id'  =>  $model->user_uuid,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            'is_pending_update'  =>  $model->is_pending_update,
            ]
        );
    }

    public function includeStates(VirtualMachinesPerspective $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(VirtualMachinesPerspective $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(VirtualMachinesPerspective $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(VirtualMachinesPerspective $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(VirtualMachinesPerspective $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(VirtualMachinesPerspective $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(VirtualMachinesPerspective $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(VirtualMachinesPerspective $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(VirtualMachinesPerspective $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE



}
