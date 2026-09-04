<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use NextDeveloper\IAAS\Database\Filters\VirtualMachinesQueryFilter;
use NextDeveloper\IAAS\Services\AbstractServices\AbstractVirtualMachinesService;
use NextDeveloper\IAAS\Services\VirtualMachinesService;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Temporary rollout-validation tool (see docs/elasticsearch/plan.md section 8, step 2) -
 * runs the same query-param combinations through both the DB path
 * (AbstractVirtualMachinesService::get()) and the ES path
 * (VirtualMachinesService::getFromElastic(), reached via reflection since it's
 * deliberately private - this tool observes the real production method, it doesn't
 * get its own blessed entry point) across a matrix of real filter params AND real
 * user/role contexts, diffing UUID sets and totals. Not meant to be kept long-term;
 * delete once the toggle has been flipped on in production and soaked.
 *
 * Run with no options to test as the current console/system context (effectively
 * unrestricted). Pass --user-id/--account-id to test as a specific real user - find
 * candidates with real VMs via:
 *   select iam_users.uuid, iam_user_roles.iam_account_id, iam_roles.name
 *   from iam_user_roles
 *   join iam_roles on iam_roles.id = iam_user_roles.iam_role_id
 *   join iam_users on iam_users.id = iam_user_roles.iam_user_id
 *   where iam_user_roles.is_active = true
 */
class DiffVirtualMachinesElastic extends Command
{
    protected $signature = 'leo:diff-virtual-machines-elastic {--user-id=} {--account-id=} {--role-label=context}';

    protected $description = 'Diffs the DB path vs the Elasticsearch path for VirtualMachines list queries across a matrix of filter params, for one user/role context.';

    private const FILTER_SCENARIOS = [
        'no filters' => [],
        'status=running' => ['status' => 'running'],
        'status=halted' => ['status' => 'halted'],
        'status=draft' => ['status' => 'draft'],
        'is_draft=1' => ['is_draft' => '1'],
        'is_locked=0' => ['is_locked' => '0'],
        'cpu=4' => ['cpu' => '4'],
        'name substring' => ['name' => 'a'],
        'sort created_at desc' => ['sort' => 'created_at|desc'],
        'sort name asc (text field)' => ['sort' => 'name|asc'],
        'sort cpu desc' => ['sort' => 'cpu|desc'],
        'created_at range' => ['created_at_start' => '2020-01-01', 'created_at_end' => '2035-01-01'],
        'paginate page 1' => ['paginate' => '1', 'per_page' => '10', 'page' => '1'],
        'paginate page 2' => ['paginate' => '1', 'per_page' => '10', 'page' => '2'],
        'rowCount=all' => ['rowCount' => 'all'],
    ];

    public function handle(): int
    {
        if ($userId = $this->option('user-id')) {
            UserHelper::setUserById((int) $userId);
        }

        if ($accountId = $this->option('account-id')) {
            UserHelper::setCurrentAccountById((int) $accountId);
        }

        $label = $this->option('role-label');

        $getFromElastic = new \ReflectionMethod(VirtualMachinesService::class, 'getFromElastic');
        $getFromElastic->setAccessible(true);

        $totalScenarios = 0;
        $mismatches = 0;

        foreach (self::FILTER_SCENARIOS as $scenarioName => $queryParams) {
            $totalScenarios++;

            $request = Request::create('/', 'GET', $queryParams);
            $filter = new VirtualMachinesQueryFilter($request);

            //  VirtualMachinesQueryFilter sees this request because it's passed to its
            //  constructor directly - but LimitScope (rowCount) and
            //  VirtualMachinesElasticQueryTranslator both read the global request()
            //  helper instead, which resolves the container-bound Request, not this
            //  local one. Binding it is what makes both paths actually see the same
            //  per-scenario params a real HTTP request would have given them.
            app()->instance('request', $request);

            try {
                $dbResult = AbstractVirtualMachinesService::get($filter, $queryParams);
            } catch (\Throwable $e) {
                $this->error("[{$label}] {$scenarioName}: DB path threw: " . $e->getMessage());
                $mismatches++;
                continue;
            }

            try {
                $esResult = $getFromElastic->invoke(null, $filter, $queryParams);
            } catch (\Throwable $e) {
                $this->error("[{$label}] {$scenarioName}: ES path threw: " . $e->getMessage());
                $mismatches++;
                continue;
            }

            $dbUuids = collect($dbResult)->pluck('uuid')->sort()->values()->all();
            $esUuids = collect($esResult)->pluck('uuid')->sort()->values()->all();

            $dbTotal = method_exists($dbResult, 'total') ? $dbResult->total() : count($dbUuids);
            $esTotal = method_exists($esResult, 'total') ? $esResult->total() : count($esUuids);

            if ($dbUuids === $esUuids && $dbTotal === $esTotal) {
                $this->line("[{$label}] {$scenarioName}: OK ({$dbTotal} results)");
            } else {
                $mismatches++;
                $this->error("[{$label}] {$scenarioName}: MISMATCH (db_total={$dbTotal} es_total={$esTotal})");

                $onlyInDb = array_values(array_diff($dbUuids, $esUuids));
                $onlyInEs = array_values(array_diff($esUuids, $dbUuids));

                if ($onlyInDb) {
                    $this->line('  only in DB: ' . implode(', ', array_slice($onlyInDb, 0, 5)) . (count($onlyInDb) > 5 ? ' ...' : ''));
                }

                if ($onlyInEs) {
                    $this->line('  only in ES: ' . implode(', ', array_slice($onlyInEs, 0, 5)) . (count($onlyInEs) > 5 ? ' ...' : ''));
                }
            }
        }

        $this->line('');
        $this->line("[{$label}] {$totalScenarios} scenarios, {$mismatches} mismatches");

        return $mismatches > 0 ? self::FAILURE : self::SUCCESS;
    }
}
