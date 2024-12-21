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
use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class NetworksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractNetworksTransformer extends AbstractTransformer
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
     * @param Networks $model
     *
     * @return array
     */
    public function transform(Networks $model)
    {
                                                $commonDomainId = \NextDeveloper\Commons\Database\Models\Domains::where('id', $model->common_domain_id)->first();
                                                            $iaasDhcpServerId = \NextDeveloper\IAAS\Database\Models\DhcpServers::where('id', $model->iaas_dhcp_server_id)->first();
                                                            $iaasGatewayId = \NextDeveloper\IAAS\Database\Models\Gateways::where('id', $model->iaas_gateway_id)->first();
                                                            $iaasNetworkPoolId = \NextDeveloper\IAAS\Database\Models\NetworkPools::where('id', $model->iaas_network_pool_id)->first();
                                                            $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('id', $model->iaas_cloud_node_id)->first();
                                                            $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                                                            $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
                        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'vlan'  =>  $model->vlan,
            'vxlan'  =>  $model->vxlan,
            'bandwidth'  =>  $model->bandwidth,
            'is_public'  =>  $model->is_public,
            'is_vpn'  =>  $model->is_vpn,
            'is_management'  =>  $model->is_management,
            'is_dmz'  =>  $model->is_dmz,
            'price_pergb'  =>  $model->price_pergb,
            'price_perip'  =>  $model->price_perip,
            'speed_limit'  =>  $model->speed_limit,
            'ip_addr'  =>  $model->ip_addr,
            'ip_addr_range_start'  =>  $model->ip_addr_range_start,
            'ip_addr_range_end'  =>  $model->ip_addr_range_end,
            'dns_nameservers'  =>  $model->dns_nameservers,
            'mtu'  =>  $model->mtu,
            'common_domain_id'  =>  $commonDomainId ? $commonDomainId->uuid : null,
            'iaas_dhcp_server_id'  =>  $iaasDhcpServerId ? $iaasDhcpServerId->uuid : null,
            'iaas_gateway_id'  =>  $iaasGatewayId ? $iaasGatewayId->uuid : null,
            'iaas_network_pool_id'  =>  $iaasNetworkPoolId ? $iaasNetworkPoolId->uuid : null,
            'iaas_cloud_node_id'  =>  $iaasCloudNodeId ? $iaasCloudNodeId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            'cidr'  =>  $model->cidr,
            ]
        );
    }

    public function includeStates(Networks $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(Networks $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(Networks $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    public function includeSocialMedia(Networks $model)
    {
        $socialMedia = SocialMedia::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($socialMedia, new SocialMediaTransformer());
    }

    public function includeComments(Networks $model)
    {
        $comments = Comments::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($comments, new CommentsTransformer());
    }

    public function includeVotes(Networks $model)
    {
        $votes = Votes::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($votes, new VotesTransformer());
    }

    public function includeMeta(Networks $model)
    {
        $meta = Meta::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($meta, new MetaTransformer());
    }

    public function includePhoneNumbers(Networks $model)
    {
        $phoneNumbers = PhoneNumbers::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($phoneNumbers, new PhoneNumbersTransformer());
    }

    public function includeAddresses(Networks $model)
    {
        $addresses = Addresses::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($addresses, new AddressesTransformer());
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}
