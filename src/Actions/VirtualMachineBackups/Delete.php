<?php

namespace NextDeveloper\IAAS\Actions\VirtualMachineBackups;

use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\IAAS\Database\Models\Accounts;
use NextDeveloper\IAAS\Database\Models\BackupJobs;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Database\Models\VirtualMachineBackups;
use NextDeveloper\IAAS\Services\RepositoriesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class Delete extends AbstractAction
{
    public const  EVENTS = [
        'deletion-started:NextDeveloper\IAAS\VirtualMachineBackups',
        'deleted:NextDeveloper\IAAS\VirtualMachineBackups',
    ];

    public const CHECKPOINTS = [
        '0'     =>  'Starting the backup process',
        '10'    =>  'Taking the snapshot of the virtual machine',
        '20'    =>  'Snapshot is taken, creating the snapshot object.',
        '30'    =>  'Fixing the name of the snapshot.',
        '40'    =>  'Converting Snapshot to VM.',
        '50'    =>  'Cloning the VM.',
        '55'    =>  'Deleting the snapshot.',
        '60'    =>  'Fixing the cloned vm name.',
        '65'    =>  'Mounting default backup repository.',
        '75'    =>  'Removing all the VIFs of cloned VM.',
        '80'    =>  'Exporting to the default backup repository.',
        '90'    =>  'VM exported, removing the cloned VM.',
        '95'    =>  'Removed VM that was cloned.',
        '100'   =>  'Virtual machine backup finished'
    ];

    /**
     * EnableService constructor.
     *
     * @param Accounts $accounts The accounts model instance.
     * @throws NotAllowedException If the action is not allowed.
     */
    public function __construct(VirtualMachineBackups $backup, $params = null, $previousAction = null)
    {
        $this->model = $backup;
        parent::__construct($params, $previousAction);
    }

    public function handle() {
        $this->setProgress(0, 'Starting to delete backup: ' . $this->model . ' which is taken: ' . $this->model->created_at);

        $repoImage = RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
            ->where('id', $this->model->iaas_repository_image_id)
            ->first();

        $repo = Repositories::withoutGlobalScopes(AuthorizationScope::class)
            ->where('id', $repoImage->iaas_repository_id)
            ->first();

        $isRemoved = RepositoriesService::deleteRepoImage($repoImage);

        $repoImage->delete();
        $this->model->delete();



        $this->setFinished('Deleted backup');
    }

}
