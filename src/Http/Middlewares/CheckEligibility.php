<?php

namespace NextDeveloper\IAAS\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAM\Helpers\UserHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckEligibility
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

        if($request->getMethod() != 'GET'){
            if(!$iamAccount->common_country_id || !$iamUser->is_profile_verified) {
                return response()->json([
                    'errors' => [
                        'status'    => 403,
                        'message'   => 'Cannot use infrastructure without country',
                        'details'   => 'Due to laws and regulations, to use infrastructure services you should have a validated account with a validated user.'
                    ],
                ], 403);
            }
        }

        /** @noinspection $next **/
        return $next($request);
    }
}
