<?php

namespace NextDeveloper\IAAS\Http\Requests\VirtualNetworkCardStats;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

class VirtualNetworkCardStatsUpdateRequest extends AbstractFormRequest
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'used_tx' => 'integer',
        'used_rx' => 'integer',
        'iaas_virtual_network_card_id' => 'nullable|exists:iaas_virtual_network_cards,uuid|uuid',
        ];
    }
    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE
}