<?php

namespace NextDeveloper\IAAS\Http\Requests\CloudNodes;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class CloudNodesCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
        'slug' => 'required|string',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_edge' => 'boolean',
        'is_alive' => 'boolean',
        'is_in_maintenance' => 'boolean',
        'position' => 'nullable|integer',
        'iaas_datacenter_id' => 'required|exists:iaas_datacenters,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

}