<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;

class SyncRepositoryService
{
    /**
     * Get the iso image files list
     *
     * @param Repositories $repo
     * @return array
     */
    public static function getIsoImages(Repositories $repo) : array
    {
        $command = 'find ' . $repo->iso_path;
        $images = self::performCommand($command, $repo);
        $images = $images['output'];

        $images = array_filter(explode("\n", trim($images)));

        $tempImages = [];

        foreach ($images as $image) {
            if(Str::contains($image, '.iso')) {
                $tempImages[] = $image;
            }
        }

        foreach ($tempImages as &$image) {
            if(Str::contains($image, '.iso')) {
                $image = str_replace($repo->iso_path . '/', '', $image);
            }
        }

        return $tempImages;
    }

    public static function syncRepoImage(RepositoryImages $images, RepositoryImages $image) : RepositoryImages
    {
        if(config('leo.debug.iaas.repo'))
            Log::info('[SyncService@syncRepoImages] Starting to sync the images for the' .
                ' repository: ' . $image->name);

        $repo = RepositoryImagesService::getRepositoryOfImage($image);

        $command = 'stat -c %s ' . $repo->vm_path . '/' . $image->filename;
        $result = self::performCommand($command, $repo);
        $size = $result['output'];

        $sizeInGb = ceil($size / 1000 / 1000);

        $image->updateQuietly([
            'size'  =>  $sizeInGb
        ]);

        return $image;
    }

    public static function syncRepoImages(Repositories $repo) : Repositories
    {
        if(config('leo.debug.iaas.repo'))
            Log::info('[SyncService@syncRepoImages] Starting to sync the images for the' .
                ' repository: ' . $repo->name);

        $command = 'find ' . $repo->vm_path;
        $images = self::performCommand($command, $repo);
        $images = $images['output'];

        $imageFiles = array_filter(explode("\n", trim($images)));

        foreach ($imageFiles as $file) {
            self::addOrUpdate($file, $repo);
        }

        $newHash = md5(time());

        self::performCommand('rm ' . $repo->vm_path . '/hash.txt -f', $repo);
        self::performCommand('echo ' . $newHash . ' > ' . $repo->vm_path . '/hash.txt', $repo);

        $repo->update([
            'last_hash' =>  $newHash
        ]);

        return $repo->fresh();
    }

    public static function performCommand($command, Repositories $repo) : ?array
    {
        if($repo->is_management_agent_available == true) {
            return $repo->performAgentCommand($command);
        } else {
            return $repo->performSSHCommand($command);
        }
    }

    public static function hashImage(Repositories $repo, RepositoryImages $image)
    {
        if(config('leo.debug.iaas.repo'))
            logger()->info('[VirtualMachineImageService@hashImage] hashing file: ' . $repo->vm_path . '/' . $image->filename);

        //  Buradan devam edelim.
    }

    public static function addOrUpdate($file, Repositories $repoServer) : ?RepositoryImages
    {
        if(config('leo.debug.iaas.repo'))
            logger()->info('[VirtualMachineImageService@addOrUpdate] File: ' . $file);

        $image = null;

        /**
         * We divide this area into two because we have exported customer vms and PlusClouds Default VMS
         */
        if (substr($file, 0, 4) == 'exp-') {
            /**
             * This section is for custom VMS. They have exp- tag at the begining of the file.
             */

            //  In this version the file should be in the database. If not then there is no such file.
            //  In this case we are just hashing the file and update it.
            $image = RepositoryImages::where('path', $file)->where('iaas_repository_id', $repoServer->id)->first();

            if($image) {
                $hash = md5($repoServer->performSSHCommand('stat -c \'%b%n%y%z\' ' . $file));

                $image->update([
                    'hash'  =>  $hash
                ]);
            }
        } else {
            /**
             * This does not have exp- tag but have .pvm in the end
             */
            if (strpos($file, 'pvm', 0)) {
                $exploded = explode('/', $file);

                $image = RepositoryImages::where('path', $file)->where('iaas_repository_id', $repoServer->id)->first();

                $command = 'stat -c \'%b%n%y%z\' ' . $file;
                $result = self::performCommand($command, $repoServer);
                $result = $result['output'];

                $hash = md5($result);

                $filename = $exploded[(count($exploded) - 1)];

                $imageName = explode('.', $filename);
                $imageType = $imageName[1];

                /**
                 * We put a "_" character check here because when we export an image we dont use _ image. We use uuid instead.
                 */
                if (strpos($imageName[0], '_')) {
                    [$os, $vmName, $vmVersion, $vmCpuVersion, $extra] = array_pad(explode('_', $imageName[0], 5), 5, null); // Eğer herhangi bir değer gelmezse null ile dolduruyoruz.

                    $os = str_replace('-', ' ', $os);
                    $vmName = str_replace('-', ' ', $vmName);
                    $vmVersion = str_replace('-', '.', $vmVersion);

                    $command = 'stat -c \'%b%n%y%z\' ' . $file;
                    $result = self::performCommand($command, $repoServer);
                    $result = $result['output'];

                    $hash = md5($result);

                    $command = 'du -shb ' . $file;
                    $size = self::performCommand($command, $repoServer);
                    $size = $size['output'];
                    $size = trim(str_replace($file, '', $size));

                    //  Böyle bir imaj yok ise kayıt et
                    if (!$image) {
                        RepositoryImagesService::create([
                            'iam_account_id' => $repoServer->iam_account_id,
                            'iam_user_id' => $repoServer->iam_user_id,
                            'iaas_repository_id' => $repoServer->id,
                            'name' => $vmName,
                            'os' => $os,
                            'distro' => $vmName,
                            'version' => $vmVersion,
                            'cpu_type' => $vmCpuVersion,
                            'extra' => $extra,
                            'default_username'  =>  'root',
                            'default_password'  =>  'Pu9vopfed1gerlmov0beto!',
                            'hash' => $hash,
                            'path' => $file,
                            'is_iso'    =>  false,
                            'supported_virtualizations' =>  ImageSupportService::getSupportedModels($imageType),
                            'is_virtual_machine_image'  =>  true,
                            'filename' => $filename,
                            'size'  =>  ceil($size / 1000 / 1000),    //  Byte to MB  As in disk storage units
                            'release_version'   =>  self::getReleaseVersion()
                        ]);
                    } else {
                        //  Var ise update edeceğiz
                        $image->update([
                            'hash'          => $hash,
                            'size'  =>  ceil($size / 1000 / 1000),    //  Byte to MB  As in disk storage units
                            'supported_virtualizations' =>  ImageSupportService::getSupportedModels($imageType),
                            'release_version'   =>  self::getReleaseVersion($image)
                        ]);
                    }
                } else {
                    $image = RepositoryImages::withoutGlobalScope(AuthorizationScope::class)
                        ->where('filename', $filename)
                        ->first();

                    if ($image) {
                        $hash = md5($repoServer->performSSHCommand('xxh128sum ' . $file));

                        $command = 'du -shb ' . $file;
                        $size = self::performCommand($command, $repoServer);
                        $size = $size['output'];
                        $size = trim(str_replace($file, '', $size));

                        $image->update([
                            'hash' => $hash,
                            'size'  =>  ceil($size / 1000 / 1000),    //  Byte to MB  As in disk storage units
                            'supported_virtualizations' =>  ImageSupportService::getSupportedModels($imageType),
                            'release_version'   => self::getReleaseVersion($image)
                        ]);
                    } else {
                        if(config('leo.debug.iaas.repo'))
                            logger()->warning('[VirtualMachineImageService@addOrUpdate] WARNING: There is an
    exported (I guess) image that I cannot sync: ' . $filename);
                    }
                }
            }
        }

        return $image;
    }

    public static function getReleaseVersion(RepositoryImages $image = null)
    {
        if(!$image)
            return 1;

        $virtualMachineImages = RepositoryImages::where('os', $image->os)
            ->where('distro', $image->distro)
            ->where('version', $image->version)
            ->where('iam_account_id', $image->iam_account_id)
            ->where('iaas_repository_id', $image->iaas_repository_id)
            //  We are discarding image with the same hash because we will evantually add +1 to the image count
            ->where('hash', '!=', $image->hash)
            ->orderBy('id', 'asc')
            ->count();

        return $virtualMachineImages + 1;
    }
}
