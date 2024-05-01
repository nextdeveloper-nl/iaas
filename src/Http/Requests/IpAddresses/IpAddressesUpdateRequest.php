<?php

namespace NextDeveloper\IAAS\Http\Requests\IpAddresses;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class IpAddressesUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'ip_addr' => 'nullable',
        'is_reserved' => 'boolean',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}