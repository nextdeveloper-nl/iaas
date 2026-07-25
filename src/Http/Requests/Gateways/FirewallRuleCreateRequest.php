<?php

namespace NextDeveloper\IAAS\Http\Requests\Gateways;

use NextDeveloper\Commons\Http\Requests\AbstractFormRequest;

/**
 * Driver-agnostic firewall rule shape - see NextDeveloper\IAAS\ValueObjects\GatewayFirewallRule.
 * Each gateway driver translates this into its own backend's native payload.
 */
class FirewallRuleCreateRequest extends AbstractFormRequest
{
    public function rules()
    {
        return [
            'action' => 'required|string|in:pass,block,reject',
            'protocol' => 'required|string',
            'source' => 'nullable|string',
            'destination' => 'nullable|string',
            'port' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
