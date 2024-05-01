<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\NetworkMembersInterfaces;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class NetworkMembersInterfacesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractNetworkMembersInterfacesTransformer extends AbstractTransformer
{

    /**
     * @param NetworkMembersInterfaces $model
     *
     * @return array
     */
    public function transform(NetworkMembersInterfaces $model)
    {
                        $iaasNetworkMemberId = \NextDeveloper\IAAS\Database\Models\NetworkMembers::where('id', $model->iaas_network_member_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'ip_addr'  =>  $model->ip_addr,
            'configuration'  =>  $model->configuration,
            'iaas_network_member_id'  =>  $iaasNetworkMemberId ? $iaasNetworkMemberId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
