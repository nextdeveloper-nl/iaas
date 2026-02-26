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
use NextDeveloper\IAAS\Database\Models\KpiPerformance;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class KpiPerformanceTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractKpiPerformanceTransformer extends AbstractTransformer
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
     * @param KpiPerformance $model
     *
     * @return array
     */
    public function transform(KpiPerformance $model)
    {
            
        return $this->buildPayload(
            [
            'id'  =>  $model->id,
            'active_clouds'  =>  $model->active_clouds,
            'active_clouds_delta'  =>  $model->active_clouds_delta,
            'active_clouds_delta_pct'  =>  $model->active_clouds_delta_pct,
            'compute_vcpus'  =>  $model->compute_vcpus,
            'compute_vcpus_delta'  =>  $model->compute_vcpus_delta,
            'compute_vcpus_delta_pct'  =>  $model->compute_vcpus_delta_pct,
            'storage_pb'  =>  $model->storage_pb,
            'storage_pb_delta'  =>  $model->storage_pb_delta,
            'storage_pb_delta_pct'  =>  $model->storage_pb_delta_pct,
            'active_tenants'  =>  $model->active_tenants,
            'active_tenants_delta'  =>  $model->active_tenants_delta,
            'active_tenants_delta_pct'  =>  $model->active_tenants_delta_pct,
            'alarm_count'  =>  $model->alarm_count,
            'alarm_count_delta'  =>  $model->alarm_count_delta,
            'alarm_count_delta_pct'  =>  $model->alarm_count_delta_pct,
            'alarm_critical_count'  =>  $model->alarm_critical_count,
            'alarm_high_count'  =>  $model->alarm_high_count,
            'alarm_low_count'  =>  $model->alarm_low_count,
            'alarm_compute_members_count'  =>  $model->alarm_compute_members_count,
            'alarm_storage_members_count'  =>  $model->alarm_storage_members_count,
            'alarm_network_members_count'  =>  $model->alarm_network_members_count,
            'alarm_virtual_machines_count'  =>  $model->alarm_virtual_machines_count,
            'bandwidth_gbps'  =>  $model->bandwidth_gbps,
            'bandwidth_gbps_delta'  =>  $model->bandwidth_gbps_delta,
            'bandwidth_gbps_delta_pct'  =>  $model->bandwidth_gbps_delta_pct,
            ]
        );
    }

    public function includeStates(KpiPerformance $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(KpiPerformance $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(KpiPerformance $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(KpiPerformance $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(KpiPerformance $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(KpiPerformance $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(KpiPerformance $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(KpiPerformance $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(KpiPerformance $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE


}
