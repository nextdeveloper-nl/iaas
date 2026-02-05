<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class SyncDockerRegistryService
{
    public static function syncRepoImages(Repositories $repo) : Repositories
    {
        $images = DockerRegistryService::getDockerImages($repo);

        foreach ($images as $repoImage) {
            $tags = DockerRegistryService::getDockerImageTags($repo, $repoImage);
            foreach ($tags as $tag) {
                $data = [
                    'name'  => $repoImage . '@' . $tag,
                    'path'  => $repo->vm_path . '/v2/' . $repoImage . '/manifests/' . $tag,
                    'filename'  => $repoImage . '@' . $tag,
                    'is_active' =>  true,
                    'is_iso' => false,
                    'is_virtual_machine_image'  =>  false,
                    'is_docker_image'   =>  true,
                    'iaas_repository_id'    =>  $repo->id,
                    'iam_account_id'    =>  $repo->iam_account_id,
                    'iam_user_id'   =>  $repo->iam_user_id,
                    'is_public' =>  false,
                    'is_cloudinit_image'    =>  false,
                    'has_plusclouds_service'    =>  false,
                    'release_version'   =>  $tag
                ];

                $existingImage = RepositoryImages::where('name', $data['name'])
                    ->where('iaas_repository_id', $repo->id)
                    ->first();

                if($existingImage) {
                    $existingImage->update($data);
                } else {
                    RepositoryImages::create($data);
                }
            }
        }
    }
}
