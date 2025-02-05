<?php

namespace NextDeveloper\IAAS\Http\Requests\Licences;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class LicencesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'object_type' => 'required|string',
        'object_id' => 'required',
        'subscription_id' => 'required|exists:marketplace_subscriptions,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}