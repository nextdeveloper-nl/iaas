<?php

namespace NextDeveloper\IAAS\Http\Requests\NetworksPerspective;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class NetworksPerspectiveCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string',
        'bandwidth' => 'nullable|integer',
        'is_dmz' => 'nullable|boolean',
        'is_public' => 'nullable|boolean',
        'price_perip' => 'nullable',
        'price_pergb' => 'nullable',
        'speed_limit' => 'nullable|integer',
        'network_pool_name' => 'nullable|string',
        'cloud_pool_name' => 'nullable|string',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}