<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\StoragePools;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class StoragePoolsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractStoragePoolsTransformer extends AbstractTransformer
{

    /**
     * @param StoragePools $model
     *
     * @return array
     */
    public function transform(StoragePools $model)
    {
                        $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('id', $model->iaas_cloud_node_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'name'  =>  $model->name,
            'gb_per_hour_price'  =>  $model->gb_per_hour_price,
            'is_active'  =>  $model->is_active,
            'iaas_cloud_node_id'  =>  $iaasCloudNodeId ? $iaasCloudNodeId->uuid : null,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'tags'  =>  $model->tags,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE











}
