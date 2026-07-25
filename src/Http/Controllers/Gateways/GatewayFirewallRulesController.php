<?php

namespace NextDeveloper\IAAS\Http\Controllers\Gateways;

use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Http\Requests\Gateways\FirewallRuleCreateRequest;
use NextDeveloper\IAAS\Services\GatewaysService;

/**
 * Self-service firewall-rule management for a gateway, routed through
 * GatewaysService -> GatewayDriverManager -> the gateway's own driver, so nothing here
 * is pfSense-specific - see NextDeveloper\IAAS\Contracts\FirewallRuleCapableInterface.
 */
class GatewayFirewallRulesController extends AbstractController
{
    public function index($ref)
    {
        $rules = array_map(fn ($r) => $r->toArray(), GatewaysService::listFirewallRules($ref));

        return ResponsableFactory::makeResponse($this, $rules);
    }

    public function store($ref, FirewallRuleCreateRequest $request)
    {
        $rule = GatewaysService::createFirewallRule($ref, $request->validated());

        return ResponsableFactory::makeResponse($this, $rule->toArray());
    }

    public function destroy($ref, $rule)
    {
        GatewaysService::deleteFirewallRule($ref, $rule);

        return $this->noContent();
    }
}
