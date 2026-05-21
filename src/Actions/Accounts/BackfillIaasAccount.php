<?php

namespace NextDeveloper\IAAS\Actions\Accounts;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAM\Database\Models\Accounts as IamAccounts;

/**
 * Backfills an IaaS account for an existing IAM account.
 *
 * The DB trigger creates child rows inactive; this action is used to manually
 * create a child row for historical IAM accounts and flags it active.
 */
class BackfillIaasAccount extends AbstractAction
{
    public const EVENTS = [
        'created:NextDeveloper\IAAS\Accounts',
    ];

    /**
     * @throws NotAllowedException
     */
    public function __construct(IamAccounts $iamAccount)
    {
        $this->model = $iamAccount;
        parent::__construct();
    }

    public function handle(): void
    {
        $this->setProgress(0, 'Starting to create iaas account');

        Accounts::withoutGlobalScopes()->firstOrCreate(
            ['iam_account_id' => $this->model->id],
            [
                'is_service_enabled' => true,
                'limits' => config('iaas.limits', []),
            ]
        );

        $this->setProgress(100, 'IaaS account created');
    }
}
