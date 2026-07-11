<?php

namespace NextDeveloper\IAAS\Console\Commands;

use Illuminate\Console\Command;
use NextDeveloper\IAAS\Services\ToolkitService;

/**
 * Meant to run once during the production Docker image build (see
 * .github/workflows/production-build.yml), right after composer install and
 * before the image is packaged - not on a running server. Downloads the
 * pinned toolkit release into public/toolkit/{version}/ so it ships inside
 * the app server's own Docker image and can be served to central ISO-repo
 * hosts that have no internet access (paired with TOOLKIT_SOURCE_URL - see
 * ToolkitService::releaseAssetUrl()).
 */
class StageToolkitForDocker extends Command
{
    /**
     * @var string
     */
    protected $signature = 'iaas:stage-toolkit-for-docker';

    /**
     * @var string
     */
    protected $description = 'Downloads the pinned toolkit release into public/toolkit/{version} so this Docker image can serve it to central ISO-repo hosts without internet access.';

    public function handle()
    {
        $path = ToolkitService::stageForDocker();

        $this->info('Staged toolkit release at: ' . $path);
    }
}
