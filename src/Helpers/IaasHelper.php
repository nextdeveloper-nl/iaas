<?php

namespace NextDeveloper\IAAS\Helpers;

use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAM\Helpers\UserHelper;

class IaasHelper
{
    public static function getAccount(\NextDeveloper\IAM\Database\Models\Accounts $account)
    {
        return Accounts::where('iam_account_id', $account->id)->first();
    }

    public static function currentAccount()
    {
        return self::getAccount(UserHelper::currentAccount());
    }

    public static function getLimits(Accounts $accounts = null)
    {
        if(!$accounts)
            $accounts = self::currentAccount();

        if(!$accounts->limits) {
            $accounts->updateQuietly([
                'limits'    =>  config('iaas.limits')
            ]);
        }

        return $accounts->fresh()->limits;
    }
}
