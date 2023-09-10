<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\sCloudNodes;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class sCloudNodesTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractsCloudNodesTransformer extends AbstractTransformer {

    /**
     * @param sCloudNodes $model
     *
     * @return array
     */
    public function transform(sCloudNodes $model) {
                        $iaasDatacenterId = \NextDeveloper\IAAS\Database\Models\sDatacenters::where('id', $model->iaas_datacenter_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
            
        return $this->buildPayload([
'id'  =>  $model->uuid,
'name'  =>  $model->name,
'slug'  =>  $model->slug,
'is_active'  =>  $model->is_active,
'is_public'  =>  $model->is_public,
'is_edge'  =>  $model->is_edge,
'is_alive'  =>  $model->is_alive == 1 ? true : false,
'maintenance_mode'  =>  $model->maintenance_mode,
'position'  =>  $model->position,
'iaas_datacenter_id'  =>  $iaasDatacenterId ? $iaasDatacenterId->uuid : null,
'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
'created_at'  =>  $model->created_at,
'updated_at'  =>  $model->updated_at,
'deleted_at'  =>  $model->deleted_at,
    ]);
    }
    
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE\n\n

}
