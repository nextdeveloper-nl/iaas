<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\IAAS\Database\Models\ComputeMemberEvents;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;

/**
 * Class ComputeMemberEventsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractComputeMemberEventsTransformer extends AbstractTransformer
{

    /**
     * @param ComputeMemberEvents $model
     *
     * @return array
     */
    public function transform(ComputeMemberEvents $model)
    {
                        $iaasComputeMemberId = \NextDeveloper\IAAS\Database\Models\ComputeMembers::where('id', $model->iaas_compute_member_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'source'  =>  $model->source,
            'type'  =>  $model->type,
            'event'  =>  $model->event,
            'iaas_compute_member_id'  =>  $iaasComputeMemberId ? $iaasComputeMemberId->uuid : null,
            'is_executed'  =>  $model->is_executed,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            ]
        );
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}