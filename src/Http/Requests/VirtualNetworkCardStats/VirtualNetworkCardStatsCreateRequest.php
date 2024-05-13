<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualNetworkCardStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualNetworkCardStatsCreateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'used_tx' => 'integer',
        'used_rx' => 'integer',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}