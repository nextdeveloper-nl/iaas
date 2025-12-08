<?php

namespace NextDeveloper\IAAS\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAM\Helpers\UserHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspension
{
    /**
     * Checks if the user is suspended. If it is suspended then we will not allow them to make a request apart from GET.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $iaasAccount = Accounts::where('iam_account_id', UserHelper::currentAccount()->id)->first();

        if($iaasAccount->is_suspended) {
            if($request->getMethod() != 'GET'){
                return response()->json([
                    'errors' => [
                        'status'    => 403,
                        'message'   => 'Suspended account',
                        'details'   => 'Cannot make this request, because you account is suspended.'
                    ],
                ], 403);
            }
        }

        /** @noinspection $next **/
        return $next($request);
    }
}
