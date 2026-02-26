<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use NextDeveloper\IAAS\Database\Filters\AccountHourlyStatsQueryFilter;
use NextDeveloper\IAAS\Database\Models\AccountHourlyStats;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractAccountHourlyStatsService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * This class is responsible from managing the data for AccountHourlyStats
 *
 * Class AccountHourlyStatsService.
 *
 * @package NextDeveloper\IAAS\Database\Models
 */
class AccountHourlyStatsService extends AbstractAccountHourlyStatsService
{

    // EDIT AFTER HERE - WARNING: ABOVE THIS LINE MAY BE REGENERATED AND YOU MAY LOSE CODE

    public static function get(AccountHourlyStatsQueryFilter $filter = null, array $params = []): Collection|LengthAwarePaginator
    {
        $account = UserHelper::currentAccount();

        $stats = AccountHourlyStats::withoutGlobalScopes()
            ->where('iam_account_id', $account->id)
            ->orderBy('stat_hour', 'desc')
            ->limit(168)
            ->get();

        return $stats;
    }
}
