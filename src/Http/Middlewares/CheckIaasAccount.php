<?php

namespace NextDeveloper\IAAS\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAAS\Helpers\IaasHelper;
use NextDeveloper\IAM\Helpers\UserHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckIaasAccount
{
    /**
     * Checks if the user is suspended. If it is suspended then we will not allow them to make a request apart from GET.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $iamAccount = UserHelper::currentAccount();
        $iamUser = UserHelper::getAccountOwner($iamAccount);

        $iaasAccount = IaasHelper::getAccount($iamAccount);

        if(!$iaasAccount->is_service_enabled) {
            return response()->json([
                'errors' => [
                    'status'    => 403,
                    'message'   => 'IAAS services are not enabled for this account',
                    'details'   => 'Please contact support to enable IAAS services for your account.'
                ],
            ], 403);
        }

        /** @noinspection $next **/
        return $next($request);
    }
}
