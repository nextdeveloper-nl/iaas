<?php

namespace NextDeveloper\IAAS\Http\Controllers\DhcpServers;

use Illuminate\Http\Request;
use NextDeveloper\IAAS\Http\Controllers\AbstractController;
use NextDeveloper\Commons\Http\Response\ResponsableFactory;
use NextDeveloper\IAAS\Database\Filters\DhcpServersQueryFilter;
use NextDeveloper\IAAS\Database\Models\DhcpServers;
use NextDeveloper\IAAS\Services\DhcpServersService;
use NextDeveloper\Commons\Http\Traits\Tags;use NextDeveloper\Commons\Http\Traits\Addresses;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class DhcpServersConfigurationController extends AbstractController
{
    private $model = DhcpServers::class;

    use Tags;
    use Addresses;
    /**
     * This method returns the list of dhcpservers.
     *
     * optional http params:
     * - paginate: If you set paginate parameter, the result will be returned paginated.
     *
     * @param  DhcpServersQueryFilter $filter  An object that builds search query
     * @param  Request                $request Laravel request object, this holds all data about request. Automatically populated.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($dhcpServer)
    {
        $dhcpServer = DhcpServers::withoutGlobalScope(AuthorizationScope::class)
            ->where('uuid', $dhcpServer)
            ->first();

        $data = DhcpServersService::getConfiguration($dhcpServer);

        return $data;
    }
}
