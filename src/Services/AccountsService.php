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

    public static function enable(Accounts $account) {
        $account->update([
            'is_service_enabled' => true
        ]);

        return $account->fresh();
    }

    public static function enableWithIamAccount(\NextDeveloper\IAM\Database\Models\Accounts|int|string $account)
    {
        $iamAccount = null;

        if($account instanceof \NextDeveloper\IAM\Database\Models\Accounts) {
            $iamAccount = $account;
        }

        if(is_int($account)) {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::withoutGlobalScope(AuthorizationScope::class)
                ->where('id', $account)
                ->first();
        }

        if(is_string($account) && Str::isUuid($account)) {
            $iamAccount = \NextDeveloper\IAM\Database\Models\Accounts::withoutGlobalScope(AuthorizationScope::class)
                ->where('uuid', $account)
                ->first();
        }

        if(!$iamAccount) {
            return null;
        }

        $iaasAccount = Accounts::withoutGlobalScope(AuthorizationScope::class)
            ->where('iam_account_id', $iamAccount->id)
            ->first();

        if(!$iaasAccount) {
            return self::create([
                'iam_account_id' => $iamAccount->uuid,
                'is_service_enabled' => true,
            ]);
        }

        return self::enable($iaasAccount);
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
