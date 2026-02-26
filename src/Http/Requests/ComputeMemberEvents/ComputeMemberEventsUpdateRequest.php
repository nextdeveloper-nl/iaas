<?php

namespace NextDeveloper\IAAS\Http\Requests\ComputeMemberEvents;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class ComputeMemberEventsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'source' => 'nullable|string',
        'type' => 'nullable|string',
        'event' => 'nullable|string',
        'iaas_compute_member_id' => 'nullable|exists:iaas_compute_members,uuid|uuid',
        'is_executed' => 'boolean',
        'is_flagged' => 'boolean',
        'results' => 'nullable',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}