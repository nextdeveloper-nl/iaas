<?php

namespace NextDeveloper\IAAS\Actions\Repositories;

use Illuminate\Support\Str;
use NextDeveloper\Commons\Actions\AbstractAction;
use NextDeveloper\Commons\Helpers\StateHelper;
use NextDeveloper\Events\Services\Events;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\Repositories\SyncRepositoryService;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

/**
 * This action will scan compute member and sync all findings
 */
class SynchronizeIsos extends AbstractAction
{
    public const EVENTS = [
        'syncing-isos:NextDeveloper\IAAS\Repositories',
        'isos-synced:NextDeveloper\IAAS\Repositories',
        'cannot-sync-isos:NextDeveloper\IAAS\Repositories'
    ];

    public function __construct(Repositories $repo)
    {
        $this->model = $repo;

        $this->queue = 'iaas';

        parent::__construct();
    }

    public function handle()
    {
        $this->setProgress(0, 'Syncronizing ISO images in repository.');

        Events::fire('syncing-isos:NextDeveloper\IAAS\Repositories', $this->model);

        if(!$this->model->iso_path) {
            StateHelper::setState($this->model, 'iso_repo', 'not_configured');

            $this->model->update([
                'is_iso_repo'   =>  false
            ]);

            $this->setFinishedWithError('ISO repository not configured. You need to check' .
                ' the machine image directory, if its available or you provided the correct path.');

            Events::fire('cannot-sync-isos:NextDeveloper\IAAS\Repositories', $this->model);

            return;
        }

        $this->model->update([
            'is_iso_repo'   =>  true
        ]);

        StateHelper::setState($this->model, 'iso_repo', 'Iso repository is configured');

        $this->setProgress(20, 'Retrieving ISO images in repository.');

        $isoImages = SyncRepositoryService::getIsoImages($this->model);

        $this->setProgress(20, 'Syncing ISO images in repository.');

        $imageCount = count($isoImages);
        $step = 80 / $imageCount;
        $now = 0;

        foreach ($isoImages as $image) {
            $this->setProgress(20 + ceil($now), 'Syncing ISO image: ' . $image);

            $dbImage = RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
                ->where('iaas_repository_id', $this->model->id)
                ->where('is_iso', true)
                ->where('filename', $image)
                ->first();

            if(!$dbImage) {
                $iamAccountId = $this->model->iam_account_id;
                $iamUserId = $this->model->iam_user_id;

                $isPublic = $this->model->is_public;

                if(Str::startsWith($image, 'config-')) {
                    $uuid = str_replace('config-', '', $image);
                    $uuid = str_replace('.iso', '', $uuid);

                    $vm = \NextDeveloper\IAAS\Database\Models\VirtualMachines::withoutGlobalScope(AuthorizationScope::class)
                        ->where('uuid', $uuid)
                        ->first();

                    if($vm) {
                        $iamAccountId = $vm->iam_account_id;
                        $iamUserId = $vm->iam_user_id;
                        $isPublic = false;
                    }
                }

                $dbImage = RepositoryImagesService::create([
                    'iaas_repository_id'    =>  $this->model->id,
                    'name'                  =>  Str::remove('.iso', $image),
                    'filename'              =>  $image,
                    'path'                  =>  $this->model->iso_path . '/' . $image,
                    'is_iso'                =>  true,
                    'is_cloudinit_image'         =>  Str::startsWith($image, 'config-'),
                    'is_public'             =>  $isPublic,
                    'ram'                   =>  1,
                    'cpu'                   =>  2,
                    'iam_account_id'        =>  $iamAccountId,
                    'iam_user_id'           =>  $iamUserId
                ]);
            }

            $now = $now + $step;
        }

        $this->setFinished('Storage member scanned and synced');
    }
}
