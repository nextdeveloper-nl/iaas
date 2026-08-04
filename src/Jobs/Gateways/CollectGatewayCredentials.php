<?php

namespace NextDeveloper\IAAS\Jobs\Gateways;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NextDeveloper\IAAS\Database\Models\Gateways;
use NextDeveloper\IAAS\Services\Hypervisors\GatewayDriverManager;
use NextDeveloper\IAM\Helpers\UserHelper;

/**
 * Waits for a freshly provisioned gateway's VM to be reachable, then runs its driver's
 * bootstrap() to populate ssh_username/ssh_password/api_token/api_url on the Gateways
 * row - moved out of the synchronous provisioning request (GatewaysService::
 * provisionForNetwork) since it can't wait minutes for a VM to boot. Retries with
 * backoff rather than failing outright, since "not booted yet" is the expected state on
 * the first few attempts.
 */
class CollectGatewayCredentials implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;

    private Gateways $gateway;

    public function __construct(Gateways $gateway)
    {
        $this->gateway = $gateway;

        $this->queue = 'iaas-config';
    }

    public function backoff(): array
    {
        return [60, 60, 120, 120, 180, 180, 300, 300, 300, 300];
    }

    public function handle()
    {
        UserHelper::setAdminAsCurrentUser();

        $driver = app(GatewayDriverManager::class)->getAdapter($this->gateway);

        if (!$driver) {
            Log::warning(__METHOD__ . " | No driver registered for gateway {$this->gateway->uuid} (gateway_type={$this->gateway->gateway_type}) - skipping bootstrap.");
            return;
        }

        $status = $driver->bootstrap($this->gateway);

        if (!$status->reachable) {
            Log::info(__METHOD__ . " | Gateway {$this->gateway->uuid} not reachable yet ({$status->message}) - will retry.");

            $this->release($this->backoff()[$this->attempts() - 1] ?? 300);

            return;
        }

        Log::info(__METHOD__ . " | Gateway {$this->gateway->uuid} bootstrapped successfully.");
    }
}
