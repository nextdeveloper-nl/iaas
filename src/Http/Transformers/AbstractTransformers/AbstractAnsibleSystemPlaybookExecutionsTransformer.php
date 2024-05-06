<?php

namespace NextDeveloper\IAAS\Http\Transformers\AbstractTransformers;

use NextDeveloper\Commons\Database\Models\Media;
use NextDeveloper\Commons\Http\Transformers\MediaTransformer;
use NextDeveloper\Commons\Database\Models\AvailableActions;
use NextDeveloper\Commons\Http\Transformers\AvailableActionsTransformer;
use NextDeveloper\Commons\Database\Models\States;
use NextDeveloper\Commons\Http\Transformers\StatesTransformer;
use NextDeveloper\IAAS\Database\Models\AnsibleSystemPlaybookExecutions;
use NextDeveloper\Commons\Http\Transformers\AbstractTransformer;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * Class AnsibleSystemPlaybookExecutionsTransformer. This class is being used to manipulate the data we are serving to the customer
 *
 * @package NextDeveloper\IAAS\Http\Transformers
 */
class AbstractAnsibleSystemPlaybookExecutionsTransformer extends AbstractTransformer
{

    /**
     * @var array
     */
    protected array $availableIncludes = [
        'states',
        'actions',
        'media'
    ];

    /**
     * @param AnsibleSystemPlaybookExecutions $model
     *
     * @return array
     */
    public function transform(AnsibleSystemPlaybookExecutions $model)
    {
                        $iaasAnsibleSystemPlaysId = \NextDeveloper\IAAS\Database\Models\AnsibleSystemPlays::where('id', $model->iaas_ansible_system_plays_id)->first();
                    $iamAccountId = \NextDeveloper\IAM\Database\Models\Accounts::where('id', $model->iam_account_id)->first();
                    $iamUserId = \NextDeveloper\IAM\Database\Models\Users::where('id', $model->iam_user_id)->first();
        
        return $this->buildPayload(
            [
            'id'  =>  $model->uuid,
            'iaas_ansible_system_plays_id'  =>  $iaasAnsibleSystemPlaysId ? $iaasAnsibleSystemPlaysId->uuid : null,
            'last_execution_time'  =>  $model->last_execution_time,
            'package'  =>  $model->package,
            'config'  =>  $model->config,
            'execution_total_time'  =>  $model->execution_total_time,
            'last_output'  =>  $model->last_output,
            'result_ok'  =>  $model->result_ok,
            'result_unreachable'  =>  $model->result_unreachable,
            'result_failed'  =>  $model->result_failed,
            'result_skipped'  =>  $model->result_skipped,
            'result_rescued'  =>  $model->result_rescued,
            'result_ignored'  =>  $model->result_ignored,
            'iam_account_id'  =>  $iamAccountId ? $iamAccountId->uuid : null,
            'iam_user_id'  =>  $iamUserId ? $iamUserId->uuid : null,
            'created_at'  =>  $model->created_at,
            'updated_at'  =>  $model->updated_at,
            'deleted_at'  =>  $model->deleted_at,
            ]
        );
    }

    public function includeStates(AnsibleSystemPlaybookExecutions $model)
    {
        $states = States::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($states, new StatesTransformer());
    }

    public function includeActions(AnsibleSystemPlaybookExecutions $model)
    {
        $input = get_class($model);
        $input = str_replace('\\Database\\Models', '', $input);

        $actions = AvailableActions::withoutGlobalScope(AuthorizationScope::class)
            ->where('input', $input)
            ->get();

        return $this->collection($actions, new AvailableActionsTransformer());
    }

    public function includeMedia(Datacenters $model)
    {
        $media = Media::where('object_type', get_class($model))
            ->where('object_id', $model->id)
            ->get();

        return $this->collection($media, new MediaTransformer());
    }

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}
