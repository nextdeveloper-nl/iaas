<?php

namespace NextDeveloper\IAAS\Http\Requests\Gateways;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

/**
 * Driver-agnostic NAT port-forward shape - see NextDeveloper\IAAS\ValueObjects\GatewayPortForward.
 * Each gateway driver translates this into its own backend's native payload.
 */
class PortForwardCreateRequest extends AbstractFormRequest
{
    public function rules()
    {
        return [
            'interface' => 'nullable|string',
            'protocol' => 'required|string|in:tcp,udp',
            'external_port' => 'required|integer|min:1|max:65535',
            'internal_ip' => 'required|ip',
            'internal_port' => 'required|integer|min:1|max:65535',
            'description' => 'nullable|string',
        ];
    }
}
