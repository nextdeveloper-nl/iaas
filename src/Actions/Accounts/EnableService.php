<?php

namespace NextDeveloper\IAAS\Actions\Accounts;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\IAAS\Database\Models\Accounts;

/**
 * This class handles the enabling of a service for an account.
 */
class EnableService extends AbstractAction
{
    /**
     * Events related to enabling the service.
     */
    public const  EVENTS = [
        'enable-service:NextDeveloper\IAAS\Accounts'
    ];

    /**
     * EnableService constructor.
     *
     * @param Accounts $accounts The accounts model instance.
     * @throws NotAllowedException If the action is not allowed.
     */
    public function __construct(Accounts $accounts)
    {
        $this->model = $accounts;
        parent::__construct();
    }

    /**
     * Handles the enabling of the service.
     *
     * This method updates the account to set the service as enabled and logs the progress.
     */
    public function handle(): void
    {
        $this->setProgress(0, 'Starting to enable service');

        $this->model->updateQuietly([
            'is_service_enabled' => true
        ]);

        $this->setProgress(100, 'Service enabled');
    }
}
