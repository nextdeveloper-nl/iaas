<?php

namespace NextDeveloper\IAAS\Helpers;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Communication\Helpers\Communicate;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAM\Helpers\UserHelper;

class IaasHelper
{
    public static function getAccount(\NextDeveloper\IAM\Database\Models\Accounts $account) : Accounts
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

    public static function notifyCloudMaintainer($subject = '', $notification = '', $object)
    {
        if(!$object) {
            Log::error('Cannot make notification because object is empty');
            return null;
        }

        $user = UserHelper::getUserWithId(
            userId: $object->iam_user_id,
            skipAccessCheck: true
        );

        (new Communicate($user))->sendNotification(
            subject: $subject,
            message: $notification
        );
    }
}
