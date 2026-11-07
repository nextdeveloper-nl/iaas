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

        //  On curl failure we used to die() here, which Octane turns into an opaque DdException
        //  with no message reaching the client. Log the real curl error and throw instead.
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            Log::error('DockerRegistryService::getDockerImages curl error', [
                'repo_uuid' => $repo->uuid,
                'error' => $error,
            ]);
            throw new Exception("Could not reach docker registry: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('DockerRegistryService::getDockerImages unexpected HTTP code', [
                'repo_uuid' => $repo->uuid,
                'http_code' => $httpCode,
            ]);
            throw new Exception("Registry returned HTTP $httpCode");
        }

        $data = json_decode($response, true);

        return $data['repositories'] ?? [];
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

        //  This method previously had no error handling at all: a curl failure or non-200
        //  response silently produced an empty tag list with no way to tell why.
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            Log::error('DockerRegistryService::getDockerImageTags curl error', [
                'repo_uuid' => $repo->uuid,
                'image' => $image,
                'error' => $error,
            ]);
            throw new Exception("Could not reach docker registry: $error");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('DockerRegistryService::getDockerImageTags unexpected HTTP code', [
                'repo_uuid' => $repo->uuid,
                'image' => $image,
                'http_code' => $httpCode,
            ]);
            throw new Exception("Registry returned HTTP $httpCode");
        }

        $data = json_decode($response, true);
        $tags = $data['tags'] ?? [];

        usort($tags, function($a, $b) {
            return version_compare($a, $b);
        });

        return $tags;
    }

    public static function deleteDockerImage(Repositories $repo, $image, $tag)
    {
        // 1. Get manifest digest
        $manifestUrl = $repo->vm_path . '/v2/' . $image . '/manifests/' . $tag;

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
                'Accept: application/vnd.docker.distribution.manifest.v2+json',
                'Accept: application/vnd.docker.distribution.manifest.list.v2+json',
                'Accept: application/vnd.oci.image.manifest.v1+json',
                'Accept: application/vnd.oci.image.index.v1+json',
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

        if (!$digest) {
            Log::error('DockerRegistryService::deleteDockerImage digest not found', [
                'repo_uuid' => $repo->uuid,
                'image' => $image,
                'tag' => $tag,
            ]);
            throw new Exception('Digest not found');
        }

        // 2. Delete manifest
        $deleteUrl = $repo->vm_path . '/v2/' . $image . '/manifests/' . $digest;
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
            Log::error('DockerRegistryService::deleteDockerImage delete failed', [
                'repo_uuid' => $repo->uuid,
                'image' => $image,
                'tag' => $tag,
                'http_code' => $status,
            ]);
            throw new Exception("Delete failed, HTTP $status");
        }

        return true;
    }

    /**
     * Runs a step-by-step connectivity/auth check against the docker registry and reports
     * exactly which step failed, instead of the registry endpoints failing opaquely.
     *
     * @param Repositories $repo
     * @return array
     */
    public static function diagnose(Repositories $repo) : array
    {
        $checks = [];

        $checks[] = [
            'name'      =>  'config',
            'status'    =>  ($repo->is_docker_registry && !empty($repo->vm_path)) ? 'pass' : 'fail',
            'message'   =>  !$repo->is_docker_registry
                ?   'Repository is not flagged as a docker registry (is_docker_registry = false)'
                :   (empty($repo->vm_path) ? 'Repository has no vm_path (registry URL) configured' : 'OK'),
        ];

        //  If there is no URL to test, every network check below would just fail identically,
        //  so we stop here and report the rest as skipped.
        if (empty($repo->vm_path)) {
            foreach (['dns', 'tcp_connect', 'tls', 'auth', 'catalog'] as $name) {
                $checks[] = [ 'name' => $name, 'status' => 'skipped', 'message' => 'Skipped because vm_path is not configured' ];
            }

            return self::buildDiagnosisResult($repo, $checks);
        }

        $host = parse_url($repo->vm_path, PHP_URL_HOST);
        $scheme = parse_url($repo->vm_path, PHP_URL_SCHEME);

        if (!$host) {
            $checks[] = [ 'name' => 'dns', 'status' => 'fail', 'message' => "Could not parse a host from vm_path: {$repo->vm_path}" ];

            foreach (['tcp_connect', 'tls', 'auth', 'catalog'] as $name) {
                $checks[] = [ 'name' => $name, 'status' => 'skipped', 'message' => 'Skipped because vm_path host could not be parsed' ];
            }

            return self::buildDiagnosisResult($repo, $checks);
        }

        $checks[] = self::checkDns($host);
        $checks[] = self::checkTcpConnect($repo->vm_path);
        $checks[] = self::checkTls($repo->vm_path, $scheme);
        $checks[] = self::checkAuth($repo);
        $checks[] = self::checkCatalog($repo);

        return self::buildDiagnosisResult($repo, $checks);
    }

    protected static function buildDiagnosisResult(Repositories $repo, array $checks) : array
    {
        $hasFailure = collect($checks)->contains(fn($check) => $check['status'] === 'fail');

        return [
            'repository'    =>  [
                'uuid'                  =>  $repo->uuid,
                'vm_path'               =>  $repo->vm_path,
                'is_docker_registry'    =>  $repo->is_docker_registry,
                'docker_registry_port'  =>  $repo->docker_registry_port,
            ],
            'checks'    =>  $checks,
            'status'    =>  $hasFailure ? 'unhealthy' : 'healthy',
        ];
    }

    protected static function checkDns($host) : array
    {
        //  checkdnsrr fails on plain IPs, so treat a valid IP as an automatic pass
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [ 'name' => 'dns', 'status' => 'pass', 'message' => "$host is an IP address, DNS not needed" ];
        }

        $resolved = checkdnsrr($host, 'A') || checkdnsrr($host, 'AAAA');

        return [
            'name'      =>  'dns',
            'status'    =>  $resolved ? 'pass' : 'fail',
            'message'   =>  $resolved ? "Resolved $host" : "Could not resolve host: $host",
        ];
    }

    protected static function checkTcpConnect($vmPath) : array
    {
        $ch = curl_init($vmPath);

        curl_setopt_array($ch, [
            CURLOPT_CONNECT_ONLY   =>  true,
            CURLOPT_TIMEOUT        =>  10,
        ]);

        curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'name'      =>  'tcp_connect',
            'status'    =>  $errno === 0 ? 'pass' : 'fail',
            'message'   =>  $errno === 0 ? 'TCP connection established' : "Could not connect: $error",
        ];
    }

    protected static function checkTls($vmPath, $scheme) : array
    {
        if ($scheme !== 'https') {
            return [ 'name' => 'tls', 'status' => 'skipped', 'message' => 'Registry URL is not https' ];
        }

        $ch = curl_init($vmPath . '/v2/');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_SSL_VERIFYPEER  =>  true,
            CURLOPT_SSL_VERIFYHOST  =>  2,
            CURLOPT_TIMEOUT         =>  10,
            CURLOPT_NOBODY          =>  true,
        ]);

        curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'name'      =>  'tls',
            'status'    =>  $errno === 0 ? 'pass' : 'fail',
            'message'   =>  $errno === 0 ? 'TLS certificate verified' : "TLS verification failed: $error",
        ];
    }

    protected static function checkAuth(Repositories $repo) : array
    {
        $ch = curl_init($repo->vm_path . '/v2/');

        $options = [
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_TIMEOUT         =>  10,
        ];

        if ($repo->registry_username) {
            $options[CURLOPT_USERPWD] = $repo->registry_username . ':' . decrypt($repo->registry_password);
        }

        curl_setopt_array($ch, $options);
        curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            return [ 'name' => 'auth', 'status' => 'fail', 'message' => "Request failed: $error" ];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return [ 'name' => 'auth', 'status' => 'fail', 'message' => "Authentication rejected, HTTP $httpCode" ];
        }

        if ($httpCode !== 200) {
            return [ 'name' => 'auth', 'status' => 'fail', 'message' => "Unexpected HTTP code from /v2/: $httpCode" ];
        }

        return [ 'name' => 'auth', 'status' => 'pass', 'message' => 'Authenticated successfully' ];
    }

    protected static function checkCatalog(Repositories $repo) : array
    {
        $ch = curl_init($repo->vm_path . '/v2/_catalog');

        $options = [
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_TIMEOUT         =>  10,
        ];

        if ($repo->registry_username) {
            $options[CURLOPT_USERPWD] = $repo->registry_username . ':' . decrypt($repo->registry_password);
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTimeMs = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
        curl_close($ch);

        if ($errno !== 0) {
            return [ 'name' => 'catalog', 'status' => 'fail', 'message' => "Request failed: $error" ];
        }

        if ($httpCode !== 200) {
            return [ 'name' => 'catalog', 'status' => 'fail', 'message' => "Unexpected HTTP code from /v2/_catalog: $httpCode", 'response_time_ms' => $totalTimeMs ];
        }

        $data = json_decode($response, true);

        if (!is_array($data) || !array_key_exists('repositories', $data)) {
            return [ 'name' => 'catalog', 'status' => 'fail', 'message' => 'Response did not contain a repositories list', 'response_time_ms' => $totalTimeMs ];
        }

        return [
            'name'              =>  'catalog',
            'status'            =>  'pass',
            'message'           =>  'Catalog fetched, ' . count($data['repositories']) . ' repositories found',
            'response_time_ms'  =>  $totalTimeMs,
        ];
    }
}
