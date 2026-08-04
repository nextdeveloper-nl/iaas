<?php

namespace NextDeveloper\IAAS\Actions\NetworkMembers;

use Illuminate\Support\Facades\Log;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\NetworkMembers;
use NextDeveloper\IAAS\Services\NetworkMembersService;

/**
 * Connects to a switch over SSH and reads the arp table of every vlan interface, looking
 * for ip addresses that are being answered for on the wire by more than one mac address, or
 * by a mac address that does not match what our own records say should own that ip. Both
 * situations only happen when an ip has been assigned manually outside of our provisioning
 * system, so this is the signal we use to detect manual/duplicate ip collisions.
 *
 * The actual scan lives in NetworkMembersService::detectIpCollisions() so it can also be
 * run synchronously (e.g. from a console command) without going through the queue.
 */
class DetectIpCollisions extends AbstractAction
{
    private $vlan;

    public const EVENTS = [
        'detecting-ip-collisions:NextDeveloper\IAAS\NetworkMembers',
        'detected-ip-collisions:NextDeveloper\IAAS\NetworkMembers',
        'ip-collision-found:NextDeveloper\IAAS\NetworkMembers',
    ];

    public const PARAMS = [
        'vlan' =>  'nullable|integer',
    ];

    public function __construct(NetworkMembers $switch, $params = null, $previous = null)
    {
        if ($params && array_key_exists(0, $params)) {
            $params = $params[0];
        }

        $this->model = $switch;
        $this->vlan = $params['vlan'] ?? null;

        $this->queue = 'iaas';

        parent::__construct($params, $previous);
    }

    public function handle()
    {
        $this->setProgress(0, 'IP collision scan starting for switch ' . $this->model->name);

        Events::fire('detecting-ip-collisions:NextDeveloper\IAAS\NetworkMembers', $this->model);

        $collisions = NetworkMembersService::detectIpCollisions($this->model, $this->vlan, function ($message) {
            Log::info('[DetectIpCollisions] ' . $message);
        });

        foreach ($collisions as $collision) {
            Events::fire('ip-collision-found:NextDeveloper\IAAS\NetworkMembers', $this->model, $collision);
        }

        Events::fire('detected-ip-collisions:NextDeveloper\IAAS\NetworkMembers', $this->model, [
            'collisions'    =>  $collisions,
        ]);

        $this->setProgress(100, 'IP collision scan completed for switch ' . $this->model->name . '. ' .
            count($collisions) . ' collision(s) found.');

        return $collisions;
    }
}
