<?php

namespace NextDeveloper\IAAS\Http\Transformers;

use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAAS\Database\Models\CustomerResourcesPerspective;

/**
 * Class CustomerResourcesPerspectiveTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class CustomerResourcesPerspectiveTransformer extends AbstractTransformer
{
    /**
     * @param CustomerResourcesPerspective $model
     *
     * @return array
     */
    public function transform(CustomerResourcesPerspective $model)
    {
        $iaasCloudNodeId = \NextDeveloper\IAAS\Database\Models\CloudNodes::where('id', $model->iaas_cloud_node_id)->first();

        $data = [
            'id'                     => $model->resource_uuid,
            'iam_account_id'         => $model->account_uuid,
            'account_name'           => $model->account_name,
            'user_name'              => $model->user_name,
            'user_email'             => $model->user_email,
            'is_account_suspended'   => $model->is_account_suspended,
            'is_crm_suspended'       => $model->is_crm_suspended,
            'is_crm_disabled'        => $model->is_crm_disabled,
            'is_accounting_disabled' => $model->is_accounting_disabled,
            'iaas_cloud_node_id'     => $iaasCloudNodeId?->uuid,
            'resource_type'          => $model->resource_type,
            'resource_uuid'          => $model->resource_uuid,
            'resource_name'          => $model->resource_name,
            'resource_status'        => $model->resource_status,
            'cpu'                    => $model->cpu,
            'ram'                    => $model->ram,
            'created_at'             => $model->created_at,
            'deleted_at'             => $model->deleted_at,
        ];

        return $this->buildPayload($data);
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}