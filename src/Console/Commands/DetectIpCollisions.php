<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\NetworkMembers\DetectIpCollisions as DetectIpCollisionsAction;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Services\NetworkMembersService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class DetectIpCollisions extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:detect-ip-collisions
        {--vlan= : Only scan this vlan number on each switch}
        {--switch= : Only scan the switch with this uuid}
        {--queue : Dispatch onto the iaas queue instead of running (and printing output) right here}';

    /**
     * @var string
     */
    protected $description = 'Connects to every configured switch and checks its arp tables for manually assigned/duplicate ip collisions.';

    public function handle() {
        $vlan = $this->option('vlan');
        $switchUuid = $this->option('switch');
        $queue = $this->option('queue');

        $switches = NetworkMembers::withoutGlobalScope(AuthorizationScope::class)
            ->withoutGlobalScope(LimitScope::class)
            ->whereNotNull('switch_type')
            ->when($switchUuid, function ($query) use ($switchUuid) {
                $query->where('uuid', $switchUuid);
            })
            ->get();

        if ($switchUuid && $switches->isEmpty()) {
            $this->error('No switch found with uuid: ' . $switchUuid);

            return;
        }

        if ($switches->isEmpty()) {
            $this->error('No switches with a switch_type configured were found.');

            return;
        }

        $this->line('Scanning ' . $switches->count() . ' switch(es)' . ($vlan ? ' (vlan ' . $vlan . ' only)' : '') .
            ($queue ? ' - dispatched onto the iaas queue.' : ' - running in the foreground.'));

        $allCollisions = [];

        foreach ($switches as $switch) {
            //  We are setting this because we want this action to be able run all the times!!!!
            UserHelper::setUserById($switch->iam_user_id);
            UserHelper::setCurrentAccountById($switch->iam_account_id);

            if ($queue) {
                Log::info(__METHOD__ . ' | Dispatching scan for switch: ' . $switch->uuid);

                try {
                    $job = new DetectIpCollisionsAction($switch, $vlan ? ['vlan' => $vlan] : null);

                    dispatch($job)->onQueue('iaas');

                    $this->line('Dispatched scan for switch ' . $switch->name . ' (' . $switch->uuid . ')');
                } catch (\Exception $e) {
                    $this->error('Could not dispatch scan for switch ' . $switch->uuid . ': ' . $e->getMessage());
                    Log::error(__METHOD__ . ' | Having a problem scanning switch: ' . $switch->uuid . ' - ' . $e->getMessage());
                }

                continue;
            }

            $this->line('');
            $this->line('== Switch: ' . $switch->name . ' (' . $switch->uuid . ') ==');

            try {
                $collisions = NetworkMembersService::detectIpCollisions(
                    $switch,
                    $vlan ? (int) $vlan : null,
                    fn ($message) => $this->line($message)
                );

                $allCollisions = array_merge($allCollisions, $collisions);
            } catch (\Exception $e) {
                $this->error('Failed to scan switch ' . $switch->uuid . ': ' . $e->getMessage());
                Log::error(__METHOD__ . ' | Having a problem scanning switch: ' . $switch->uuid . ' - ' . $e->getMessage());
            }
        }

        if ($queue) {
            return;
        }

        $this->line('');

        if (empty($allCollisions)) {
            $this->info('No ip collisions found.');

            return;
        }

        $this->warn(count($allCollisions) . ' ip collision(s) found:');

        $this->table(
            ['IP', 'MAC(s)', 'Reason', 'Interface', 'Network ID'],
            array_map(fn ($c) => [$c['ip'], implode(', ', $c['macs']), $c['reason'], $c['interface'], $c['network_id']], $allCollisions)
        );
    }
}
