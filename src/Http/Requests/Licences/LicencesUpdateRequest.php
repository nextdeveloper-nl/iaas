<?php

namespace NextDeveloper\IAAS\Http\Requests\Licences;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class LicencesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'nullable|string',
        'object_id' => 'nullable',
        'subscription_id' => 'nullable|exists:marketplace_subscriptions,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}