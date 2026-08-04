<?php

namespace NextDeveloper\IAAS\Http\Controllers\Gateways;

use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\IAAS\Http\Requests\Gateways\PortForwardCreateRequest;
use NextDeveloper\IAAS\Services\GatewaysService;

/**
 * Self-service NAT/port-forward management for a gateway, routed through
 * GatewaysService -> GatewayDriverManager -> the gateway's own driver, so nothing here
 * is pfSense-specific - see NextDeveloper\IAAS\Contracts\NatCapableInterface.
 */
class GatewayPortForwardsController extends AbstractController
{
    public function index($ref)
    {
        $forwards = array_map(fn ($f) => $f->toArray(), GatewaysService::listPortForwards($ref));

        return ResponsableFactory::makeResponse($this, $forwards);
    }

    public function store($ref, PortForwardCreateRequest $request)
    {
        $forward = GatewaysService::createPortForward($ref, $request->validated());

        return ResponsableFactory::makeResponse($this, $forward->toArray());
    }

    public function destroy($ref, $forward)
    {
        GatewaysService::deletePortForward($ref, $forward);

        return $this->noContent();
    }
}
