<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractAccountsService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This class is responsible from managing the data for Accounts
 *
 * Class AccountsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AccountsService extends AbstractAccountsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function suspend(Accounts $account) {
        $account->update([
            'is_suspended' => true
        ]);

        return $account->fresh();
    }

    public static function suspendWithIamAccount(\NextDeveloper\IAM\Database\Models\Accounts|int|string $account)
    {
        if(is_int($account)) {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $account)
                ->first();

            $account = Accounts::withoutGlobalScope(AuthorizationScope::class)
                ->where('iam_account_id', $iamAccount->id)
                ->first();
        }

        if(is_string($account)) {
            if(Str::isUuid($account)) {
                $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::withoutGlobalScope(AuthorizationScope::class)
                    ->where('uuid', $account)
                    ->first();

                $account = Accounts::withoutGlobalScope(AuthorizationScope::class)
                    ->where('iam_account_id', $iamAccount->id)
                    ->first();
            }
        }

        if($account instanceof \NextDeveloper\IAM\Database\Models\Accounts) {
            $account = Accounts::withoutGlobalScope(AuthorizationScope::class)
                ->where('iam_account_id', $account->id)
                ->first();

            return self::suspend($account);
        }
    }
}
