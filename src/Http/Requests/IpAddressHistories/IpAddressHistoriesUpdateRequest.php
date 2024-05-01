<?php

namespace NextDeveloper\IAAS\Http\Requests\IpAddressHistories;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class IpAddressHistoriesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'body' => 'nullable|string',
        'hash' => 'nullable|string',
        'is_create' => 'boolean',
        'is_update' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}