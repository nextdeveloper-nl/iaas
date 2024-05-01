<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\Networks;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class NetworksTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractNetworksTransformer extends AbstractTransformer
{

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
            'ip_addr'  =>  $model->ip_addr,
            'ip_addr_range_start'  =>  $model->ip_addr_range_start,
            'ip_addr_range_end'  =>  $model->ip_addr_range_end,
            'dns_nameservers'  =>  $model->dns_nameservers,
            'mtu'  =>  $model->mtu,
            'common_domain_id'  =>  $commonDomainId ? $commonDomainId->uuid : null,
            'iaas_dhcp_server_id'  =>  $iaasDhcpServerId ? $iaasDhcpServerId->uuid : null,
            'iaas_gateway_id'  =>  $iaasGatewayId ? $iaasGatewayId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
