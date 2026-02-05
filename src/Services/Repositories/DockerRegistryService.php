<?php

namespace NextDeveloper\IAAS\Services\Repositories;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NextDeveloper\IAAS\Database\Models\Repositories;
use NextDeveloper\IAAS\Database\Models\RepositoryImages;
use NextDeveloper\IAAS\Services\RepositoryImagesService;
use NextDeveloper\IAM\Database\Scopes\AuthorizationScope;
use NextDeveloper\IAM\Helpers\UserHelper;

class DockerRegistryService
{
    /**
     * Get the iso image files list
     *
     * @param Repositories $repo
     * @return array
     */
    public static function getDockerImages(Repositories $repo) : array
    {
        $registryUrl = $repo->vm_path; // no trailing slash
        $endpoint = $registryUrl . '/v2/_catalog';

        $ch = curl_init($endpoint);

        if($repo->registry_username) {
            $username = $repo->registry_username;
            $password = decrypt($repo->registry_password);

            curl_setopt_array($ch, [
                CURLOPT_USERPWD => "$username:$password",
            ]);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            die('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            die("Registry returned HTTP $httpCode");
        }

        $data = json_decode($response, true);

        return $data['repositories'];
    }

    public static function getDockerImageTags(Repositories $repo, $image)
    {
        $registryUrl = $repo->vm_path; // no trailing slash
        $endpoint = $registryUrl . '/v2/';

        $url = $endpoint . $image . "/tags/list";

        $ch = curl_init($url);

        if($repo->registry_username) {
            $username = $repo->registry_username;
            $password = decrypt($repo->registry_password);

            curl_setopt_array($ch, [
                CURLOPT_USERPWD => "$username:$password",
            ]);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['tags'] ?? [];
    }

    public static function deleteDockerImage(RepositoryImages $image)
    {
        $repo = Repositories::where('id', $image->iaas_repository_id)->first();

        // 1. Get manifest digest
        $manifestUrl = $image->path;

        $ch = curl_init($manifestUrl);

        if($repo->registry_username) {
            $username = $repo->registry_username;
            $password = decrypt($repo->registry_password);

            curl_setopt_array($ch, [
                CURLOPT_USERPWD => "$username:$password",
            ]);
        }

        $headers = [];

        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true, // HEAD request
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.docker.distribution.manifest.v2+json, application/vnd.oci.image.manifest.v1+json'
            ],
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = trim($header);
                if ($header !== '') {
                    $headers[] = $header;
                }
                return $len;
            },
        ]);

        curl_exec($ch);
        curl_close($ch);

        $digest = null;

        foreach ($headers as $line) {
            if (stripos($line, 'Docker-Content-Digest:') !== false) {
                $digest = trim(explode(':', $line, 2)[1]);
                break;
            }
        }

        curl_close($ch);

        if (!$digest) {
            throw new Exception('Digest not found');
        }

        $imageName = explode('@', $image->filename)[0];

        // 2. Delete manifest
        $deleteUrl = $repo->vm_path . '/v2/' . $imageName . '/manifests/' . $digest;
        $ch = curl_init($deleteUrl);

        if($repo->registry_username) {
            $username = $repo->registry_username;
            $password = decrypt($repo->registry_password);

            curl_setopt_array($ch, [
                CURLOPT_USERPWD => "$username:$password",
            ]);
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true
        ]);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 202) {
            throw new Exception("Delete failed, HTTP $status");
        }

        return true;
    }
}
