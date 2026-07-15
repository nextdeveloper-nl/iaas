<?php
namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Database\GlobalScopes\LimitScope;
use NextDeveloper\IAAS\Actions\NetworkMembers\DetectIpCollisions as DetectIpCollisionsAction;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class DetectIpCollisions extends Command {
    /**
     * @var string
     */
    protected $signature = 'leo:detect-ip-collisions
        {--vlan= : Only scan this vlan number on each switch}
        {--switch= : Only scan the switch with this uuid}';

    /**
     * @var string
     */
    protected $description = 'Connects to every configured switch and checks its arp tables for manually assigned/duplicate ip collisions.';

    public function handle() {
        $vlan = $this->option('vlan');
        $switchUuid = $this->option('switch');

        $this->line('Starting ip collision scan for ' . ($switchUuid ? 'switch ' . $switchUuid : 'all switches')
            . ($vlan ? ' (vlan ' . $vlan . ' only)' : ''));
        Log::info(__METHOD__ . ' | Starting ip collision scan for ' . ($switchUuid ? 'switch ' . $switchUuid : 'all switches') . '.');

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

        foreach ($switches as $switch) {
            Log::info(__METHOD__ . ' | Scanning switch: ' . $switch->uuid);

            //  We are setting this because we want this action to be able run all the times!!!!
            UserHelper::setUserById($switch->iam_user_id);
            UserHelper::setCurrentAccountById($switch->iam_account_id);

            try {
                $job = new DetectIpCollisionsAction($switch, $vlan ? ['vlan' => $vlan] : null);

                dispatch($job)->onQueue('iaas');
            } catch (\Exception $e) {
                Log::error(__METHOD__ . ' | Having a problem scanning switch: ' . $switch->uuid . ' - ' . $e->getMessage());
            }
        }
    }
}
