<?php

namespace NextDeveloper\IAAS\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Everything needed to build a VM's configuration ISO from the shared
 * plusclouds/toolkit repository (https://github.com/plusclouds/toolkit).
 *
 * Two distinct roles, because the toolkit's content lives in two different
 * places for two different reasons:
 *
 * - Most capability files (change-password, disk-resize, ...) are static per
 *   release and are the same for every VM built on a given central ISO-repo
 *   host, so that host self-provisions its own local toolkit cache directly
 *   from GitHub (see ensureRemoteCacheCommand()/copyCommand()) - the app
 *   server never touches their bytes.
 * - A handful of files are inherently per-VM dynamic (agent.yaml needs
 *   {agent_uuid}/{api_key} substitution, and the orchestration playbook
 *   itself is generated from which capabilities this specific VM needs) - for
 *   those, the app server keeps its own small local cache (read()) so PHP can
 *   template/generate their content before pushing it, same as
 *   user-data/meta-data/pc-meta-data.json already are.
 *
 * Never reads from a branch: only from a specific release tag pinned via
 * config('iaas.toolkit.version'), so upgrading the toolkit is a deliberate
 * version bump rather than something that changes silently underneath us.
 *
 * Service roles (docker, postgresql, ...) follow the same capability
 * convention: each active iaas_ansible_roles catalog entry's `name` must
 * match a toolkit folder at capabilities/service-roles/{name}/linux.yml.
 * Which roles a VM gets is driven entirely by metadata.service_roles (see
 * VirtualMachinesMetadataService::collectServiceRoles()), never hardcoded here.
 *
 * Class ToolkitService.
 *
 * @package NextDeveloper\IAAS\Services
 */
class ToolkitService
{
    private const REPO = 'plusclouds/toolkit';

    //  Where the central ISO-repo host caches extracted toolkit releases -
    //  under the SSH user's own home directory, since these hosts are reached
    //  over SSH as a non-root user with no write access to /opt. This is a
    //  shell-side path (kept unescaped/double-quoted, never passed through
    //  escapeshellarg()) so $HOME expands in whichever user's session runs it.
    private const REMOTE_CACHE_ROOT = '$HOME/toolkit-cache';

    //  Disk-resize variant selection stays driven by the guest's own Ansible
    //  facts (ansible_facts.distribution/distribution_version), gathered at
    //  runtime - more reliable than $vm->distro, which is free text set at
    //  image-import time with no validated format across the fleet.
    private const DISK_RESIZE_DISPATCH = [
        'ubuntu22' => ['ansible_facts.distribution == "Ubuntu"', 'ansible_facts.distribution_version == "22.04"'],
        'ubuntu24' => ['ansible_facts.distribution == "Ubuntu"', 'ansible_facts.distribution_version == "24.04"'],
        'debian12' => ['ansible_facts.distribution == "Debian"'],
        'alma'     => ['ansible_facts.distribution == "AlmaLinux"'],
    ];

    public static function pinnedVersion(): string
    {
        return config('iaas.toolkit.version', 'v1.0.0');
    }

    /* =======================================================================
     * App-server-side: only for files PHP must template/generate itself
     * ===================================================================== */

    public static function read(string $relativePath): string
    {
        $releaseDir = self::ensureLocalRelease(self::pinnedVersion());

        $path = $releaseDir . '/' . $relativePath;

        if (!file_exists($path)) {
            throw new RuntimeException("ToolkitService: [{$relativePath}] not found in toolkit release [" . self::pinnedVersion() . "]");
        }

        return file_get_contents($path);
    }

    /**
     * The service roles actually available in the pinned toolkit release: each name is a
     * directory under capabilities/service-roles/ that ships a linux.yml, mapped to that role's
     * content hash (iaas_ansible_roles.hash - lets the catalog detect when a role's capability
     * content changed between toolkit versions) and its meta.yml description
     * (iaas_ansible_roles.description). meta.yml is required - a service role without one is
     * skipped rather than synced with a blank description, since the sync job would otherwise
     * silently overwrite a previously-set description with nothing.
     *
     * This is the source of truth AnsibleRolesService::syncFromToolkit() reconciles the
     * iaas_ansible_roles catalog against, so the catalog never drifts ahead of what a config ISO
     * can actually apply.
     *
     * @return array<string, array{hash: string, description: string}>
     */
    public static function discoverServiceRoleNames(): array
    {
        $serviceRolesDir = self::ensureLocalRelease(self::pinnedVersion()) . '/capabilities/service-roles';

        if (!is_dir($serviceRolesDir)) {
            return [];
        }

        $roles = [];

        foreach (scandir($serviceRolesDir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $capabilityFile = $serviceRolesDir . '/' . $entry . '/linux.yml';
            $metaFile = $serviceRolesDir . '/' . $entry . '/meta.yml';

            if (!is_file($capabilityFile) || !is_file($metaFile)) {
                continue;
            }

            $meta = yaml_parse_file($metaFile);

            if (empty($meta['description'])) {
                continue;
            }

            $roles[$entry] = [
                'hash' => hash_file('sha256', $capabilityFile),
                'description' => $meta['description'],
            ];
        }

        ksort($roles);

        return $roles;
    }

    /**
     * The list of toolkit-relative capability paths this VM's Linux config
     * needs, given which optional metadata it has. Mirrors what
     * renderLinuxPlaybook() references - keep both in sync.
     *
     * $serviceRoleNames are the VM's enabled entries from metadata.service_roles
     * (see VirtualMachinesMetadataService::collectServiceRoles()) - each name must
     * match an active iaas_ansible_roles catalog entry 1:1 with a toolkit capability
     * folder at capabilities/service-roles/{name}/linux.yml.
     */
    public static function linuxCapabilityPaths(bool $includeEnvVars, bool $includeSshKeys, array $serviceRoleNames = []): array
    {
        $paths = [
            //  Uploaded but not referenced by any include_tasks in renderLinuxPlaybook()
            //  (pre-existing: the original static apply-configuration.yml never wired it
            //  in either - kept for exact parity with today's ISO contents, not because
            //  it does anything).
            'capabilities/apply-locale/linux.yml',
            'capabilities/change-password/linux.yml',
            'capabilities/change-hostname/linux.yml',
            'capabilities/disk-resize/ubuntu22.yml',
            'capabilities/disk-resize/ubuntu24.yml',
            'capabilities/disk-resize/debian12.yml',
            'capabilities/disk-resize/alma.yml',
            'agents/vm-service/deploy-service.yml',
            'capabilities/run-post-boot-script/linux.yml',
            'capabilities/run-startup-script/linux.yml',
        ];

        //  agents/vm-service/plusclouds-agent.service is deliberately NOT in
        //  this list: deploy-service.yml `mv`s it (and agent.yaml/plusclouds.service)
        //  with `chdir: playbook_dir`, i.e. it must sit flat at the ISO root next
        //  to apply-configuration.yml - copyCommand() preserves each path's
        //  nested source directory, which would bury it under agents/vm-service/
        //  instead. It's copied separately via copyToIsoRootCommand() - see
        //  VirtualMachinesXenService::updateConfigurationIso().

        if ($includeEnvVars) {
            $paths[] = 'capabilities/apply-env-vars/linux.yml';
        }

        if ($includeSshKeys) {
            $paths[] = 'capabilities/apply-ssh-keys/linux.yml';
        }

        foreach ($serviceRoleNames as $roleName) {
            $paths[] = "capabilities/service-roles/{$roleName}/linux.yml";
        }

        return $paths;
    }

    /**
     * Renders apply-configuration.yml content dynamically - same filename
     * plusclouds.sh already hardcodes on every existing VM, so nothing on
     * the guest side needs to change, only what's inside it.
     */
    public static function renderLinuxPlaybook(bool $includeEnvVars, bool $includeSshKeys, array $serviceRoleNames = []): string
    {
        $lines = [
            '---',
            '- name: Get VM Metadata from local JSON file',
            '  hosts: all',
            '  vars:',
            '    metadata_file: "{{ playbook_dir }}/pc-meta-data.json"',
            '',
            '  tasks:',
            '    - name: Load metadata from local JSON file',
            '      set_fact:',
            "        metadata: \"{{ lookup('file', metadata_file) | from_json }}\"",
            '',
            '    - name: Extract values from metadata',
            '      set_fact:',
            '        vm_user: "{{ metadata.username }}"',
            '        vm_password: "{{ metadata.password }}"',
            '',
            '    - name: Apply password configuration',
            '      include_tasks: capabilities/change-password/linux.yml',
            '',
            '    - name: Extract values for hostname configuration',
            '      set_fact:',
            "        hostname: \"{{ metadata.hostname | default('default-hostname') }}\"",
            '',
            '    - name: Apply hostname configuration',
            '      include_tasks: capabilities/change-hostname/linux.yml',
            '',
        ];

        foreach (self::DISK_RESIZE_DISPATCH as $variant => $conditions) {
            $lines[] = '    - name: Enlarge disk';
            $lines[] = "      include_tasks: capabilities/disk-resize/{$variant}.yml";
            $lines[] = '      when:';
            foreach ($conditions as $condition) {
                $lines[] = "        - {$condition}";
            }
            $lines[] = '';
        }

        if ($includeEnvVars) {
            $lines[] = '    - name: Extract environment variables';
            $lines[] = '      set_fact:';
            $lines[] = '        env_vars: "{{ metadata.env_vars | default({}) }}"';
            $lines[] = '';
            $lines[] = '    - name: Apply environment variables';
            $lines[] = '      include_tasks: capabilities/apply-env-vars/linux.yml';
            $lines[] = '';
        }

        if ($includeSshKeys) {
            $lines[] = '    - name: Extract SSH keys';
            $lines[] = '      set_fact:';
            $lines[] = '        ssh_keys: "{{ metadata.ssh_keys | default([]) }}"';
            $lines[] = '';
            $lines[] = '    - name: Apply SSH keys';
            $lines[] = '      include_tasks: capabilities/apply-ssh-keys/linux.yml';
            $lines[] = '';
        }

        if (!empty($serviceRoleNames)) {
            $lines[] = '    - name: Extract service roles';
            $lines[] = '      set_fact:';
            $lines[] = '        service_roles: "{{ metadata.service_roles | default({}) }}"';
            $lines[] = '';

            foreach ($serviceRoleNames as $roleName) {
                //  The task name is a plain (unquoted) YAML scalar - it must not contain
                //  its own ": ", which YAML would parse as a nested mapping key and break.
                $lines[] = "    - name: Apply service role - {$roleName}";
                $lines[] = "      include_tasks: capabilities/service-roles/{$roleName}/linux.yml";
                $lines[] = '      when: service_roles[' . "'{$roleName}'" . '][' . "'enabled'" . '] | default(false)';
                $lines[] = '';
            }
        }

        $lines[] = '    - name: Deploy service configuration';
        $lines[] = '      include_tasks: agents/vm-service/deploy-service.yml';
        $lines[] = '';
        $lines[] = '    - name: Run post boot script';
        $lines[] = '      include_tasks: capabilities/run-post-boot-script/linux.yml';
        $lines[] = '';
        $lines[] = '    - name: Run image startup script';
        $lines[] = '      include_tasks: capabilities/run-startup-script/linux.yml';

        return implode("\n", $lines) . "\n";
    }

    /**
     * The list of toolkit-relative capability paths this VM's Windows config
     * needs. Mirrors what renderWindowsPlaybook() references - keep both in
     * sync.
     */
    public static function windowsCapabilityPaths(bool $includeEnvVars, bool $includeSshKeys): array
    {
        $paths = [
            'capabilities/change-hostname/windows.ps1',
            'capabilities/change-password/windows.ps1',
            'agents/windows-vm-service/register-startup-task.ps1',
        ];

        if ($includeEnvVars) {
            $paths[] = 'capabilities/apply-env-vars/windows.ps1';
        }

        if ($includeSshKeys) {
            $paths[] = 'capabilities/apply-ssh-keys/windows.ps1';
        }

        return $paths;
    }

    /**
     * Renders apply-configuration.ps1 content dynamically - same filename
     * register-startup-task.ps1 already hardcodes looking for on the mounted
     * config ISO, so nothing on the guest side needs to change.
     */
    public static function renderWindowsPlaybook(bool $includeEnvVars, bool $includeSshKeys): string
    {
        $lines = [
            '#Requires -RunAsAdministrator',
            "\$ErrorActionPreference = 'Stop'",
            '$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path',
            "\$MetaDataFile = Join-Path \$ScriptDir 'pc-meta-data.json'",
            '',
            'if (-not (Test-Path $MetaDataFile)) {',
            '    Write-Error "pc-meta-data.json not found at $MetaDataFile"',
            '    exit 1',
            '}',
            '',
            '$metadata = Get-Content $MetaDataFile -Raw | ConvertFrom-Json',
            '',
            "& (Join-Path \$ScriptDir 'capabilities\\change-hostname\\windows.ps1') -Hostname \$metadata.hostname",
            "& (Join-Path \$ScriptDir 'capabilities\\change-password\\windows.ps1') -Username \$metadata.username -Password \$metadata.password",
        ];

        if ($includeEnvVars) {
            $lines[] = "& (Join-Path \$ScriptDir 'capabilities\\apply-env-vars\\windows.ps1') -EnvVars \$metadata.env_vars";
        }

        if ($includeSshKeys) {
            $lines[] = "& (Join-Path \$ScriptDir 'capabilities\\apply-ssh-keys\\windows.ps1') -Username \$metadata.username -SshKeys \$metadata.ssh_keys";
        }

        $lines[] = '';
        $lines[] = 'Write-Host "==> Configuration applied successfully."';

        return implode("\n", $lines) . "\n";
    }

    /* =======================================================================
     * Central ISO-repo-host-side: commands run via performCommand($cmd, $centralRepo)
     * ===================================================================== */

    /**
     * Idempotent: only downloads/extracts/verifies if this version isn't
     * already cached on the repo host. Safe to run before every VM build.
     *
     * Always exits 0 and reports its outcome via a TOOLKIT_CACHE_OK/
     * TOOLKIT_CACHE_ERROR marker on stdout/stderr instead of relying on the
     * shell exit code - performSSHCommand()/performAgentCommand() don't
     * surface remote exit codes back to PHP, so a bare `set -e` here used to
     * mean curl/tar/checksum failures were silently swallowed (only ever
     * reaching a Log::debug call that's off in production) while the caller
     * carried on copying capability files from a cache dir that was never
     * actually populated. The caller must check the returned output for the
     * marker - see VirtualMachinesXenService::ensureRemoteToolkitCache().
     */
    public static function ensureRemoteCacheCommand(): string
    {
        $version = self::pinnedVersion();
        $cacheDir = self::REMOTE_CACHE_ROOT . '/' . $version;
        $checksumsUrl = self::releaseAssetUrl($version, 'checksums.sha256');
        $tarballUrl = self::releaseAssetUrl($version, "toolkit-{$version}.tar.gz");

        $lines = [
            'if [ -d "' . $cacheDir . '" ]; then',
            '  echo TOOLKIT_CACHE_OK',
            'else',
            '  tmp_dir=$(mktemp -d)',
            '  fail() { echo "TOOLKIT_CACHE_ERROR: $1" >&2; rm -rf "$tmp_dir"; exit 1; }',
            '  curl -fsSL ' . escapeshellarg($checksumsUrl) . ' -o "$tmp_dir/checksums.sha256" || fail "failed to download checksums.sha256 for ' . $version . '"',
            '  curl -fsSL ' . escapeshellarg($tarballUrl) . ' -o "$tmp_dir/toolkit.tar.gz" || fail "failed to download toolkit-' . $version . '.tar.gz"',
            '  mkdir -p "$tmp_dir/extracted"',
            '  tar -xzf "$tmp_dir/toolkit.tar.gz" -C "$tmp_dir/extracted" || fail "failed to extract toolkit tarball"',
            '  cp "$tmp_dir/checksums.sha256" "$tmp_dir/extracted/checksums.sha256"',
            '  (cd "$tmp_dir/extracted" && sha256sum -c checksums.sha256 --quiet) || fail "checksum verification failed"',
            '  rm -f "$tmp_dir/extracted/checksums.sha256"',
            '  mkdir -p "' . self::REMOTE_CACHE_ROOT . '"',
            '  mv "$tmp_dir/extracted" "' . $cacheDir . '" || fail "failed to move extracted release into cache dir"',
            '  rm -rf "$tmp_dir"',
            '  echo TOOLKIT_CACHE_OK',
            'fi',
        ];

        return implode("\n", $lines);
    }

    /**
     * Central ISO-repo hosts have no internet access, so they can't reach
     * GitHub directly - they fetch the pinned release from this app server's
     * own address instead, same as every other repo/compute-member callback
     * in this codebase (finalize-backup, finalize-commit, /public/iaas/metrics,
     * /public/iaas/ipmi - see ComputeMemberXenService/Commit.php), all built
     * off config('leo.internal_endpoint') with no GitHub-reachability
     * fallback. Requires stageForDocker() to have staged the files at build
     * time (see production-build.yml).
     */
    private static function releaseAssetUrl(string $version, string $filename): string
    {
        return rtrim(config('leo.internal_endpoint'), '/') . "/toolkit/{$version}/{$filename}";
    }

    /**
     * Downloads the pinned release's checksums.sha256 + tarball into
     * public/toolkit/{version}/ so this app server's own Docker image can
     * serve them to central ISO-repo hosts that have no internet access (see
     * releaseAssetUrl()). Meant to be run once at image build time, not
     * per-request - see the iaas:stage-toolkit-for-docker command.
     */
    public static function stageForDocker(): string
    {
        $version = self::pinnedVersion();
        $destDir = public_path('toolkit/' . $version);

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        foreach (['checksums.sha256', "toolkit-{$version}.tar.gz"] as $filename) {
            $url = 'https://github.com/' . self::REPO . "/releases/download/{$version}/{$filename}";

            $response = Http::get($url);

            if (!$response->successful()) {
                throw new RuntimeException("ToolkitService: failed to download [{$filename}] for [{$version}] while staging for docker (HTTP {$response->status()})");
            }

            file_put_contents($destDir . '/' . $filename, $response->body());
        }

        return $destDir;
    }

    /**
     * Selectively copies the given toolkit-relative paths from the repo
     * host's local cache into config-iso/{uuid}/, preserving nested
     * directories (ansible's include_tasks and PowerShell's relative script
     * calls both resolve fine against subdirectories - no flattening needed).
     */
    public static function copyCommand(array $relativePaths, string $vmUuid): string
    {
        $cacheDir = self::REMOTE_CACHE_ROOT . '/' . self::pinnedVersion();
        $destRoot = 'config-iso/' . $vmUuid;

        $lines = [];

        foreach ($relativePaths as $relativePath) {
            $destPath = $destRoot . '/' . $relativePath;
            $lines[] = 'mkdir -p ' . escapeshellarg(dirname($destPath));
            $lines[] = 'cp "' . $cacheDir . '/' . $relativePath . '" ' . escapeshellarg($destPath);
        }

        return implode("\n", $lines);
    }

    /**
     * Copies a single toolkit-relative file from the repo host's local cache
     * straight to config-iso/{uuid}/{destFilename}, discarding its nested
     * source directory - for the handful of files deploy-service.yml expects
     * as flat siblings of apply-configuration.yml (chdir: playbook_dir),
     * unlike capabilities included via their nested include_tasks path.
     */
    public static function copyToIsoRootCommand(string $relativePath, string $destFilename, string $vmUuid): string
    {
        $cacheDir = self::REMOTE_CACHE_ROOT . '/' . self::pinnedVersion();
        $destPath = 'config-iso/' . $vmUuid . '/' . $destFilename;

        return 'cp "' . $cacheDir . '/' . $relativePath . '" ' . escapeshellarg($destPath);
    }

    /* =======================================================================
     * Internals
     * ===================================================================== */

    private static function ensureLocalRelease(string $version): string
    {
        $releaseDir = storage_path('app/toolkit/' . $version);

        if (is_dir($releaseDir)) {
            return $releaseDir;
        }

        Log::info("[ToolkitService] Fetching toolkit release [{$version}] for local app-server cache");

        $manifest = self::downloadManifest($version);
        $tarball = self::downloadTarball($version);

        $tmpDir = $releaseDir . '.tmp-' . uniqid();
        mkdir($tmpDir, 0755, true);

        try {
            self::extractTarball($tarball, $tmpDir);
            self::verifyAgainstManifest($tmpDir, $manifest);
        } finally {
            @unlink($tarball);
        }

        rename($tmpDir, $releaseDir);

        return $releaseDir;
    }

    private static function downloadManifest(string $version): array
    {
        $url = 'https://github.com/' . self::REPO . "/releases/download/{$version}/manifest.json";

        $response = Http::get($url);

        if (!$response->successful()) {
            throw new RuntimeException("ToolkitService: failed to download manifest.json for [{$version}] (HTTP {$response->status()})");
        }

        return $response->json();
    }

    private static function downloadTarball(string $version): string
    {
        $url = 'https://github.com/' . self::REPO . "/releases/download/{$version}/toolkit-{$version}.tar.gz";

        $response = Http::get($url);

        if (!$response->successful()) {
            throw new RuntimeException("ToolkitService: failed to download release tarball for [{$version}] (HTTP {$response->status()})");
        }

        $tarball = tempnam(sys_get_temp_dir(), 'toolkit-') . '.tar.gz';
        file_put_contents($tarball, $response->body());

        return $tarball;
    }

    private static function extractTarball(string $tarball, string $destination): void
    {
        $command = 'tar -xzf ' . escapeshellarg($tarball) . ' -C ' . escapeshellarg($destination);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException("ToolkitService: failed to extract release tarball (exit code {$exitCode})");
        }
    }

    private static function verifyAgainstManifest(string $dir, array $manifest): void
    {
        foreach ($manifest['files'] ?? [] as $relativePath => $meta) {
            $path = $dir . '/' . $relativePath;

            if (!file_exists($path)) {
                throw new RuntimeException("ToolkitService: manifest lists [{$relativePath}] but it is missing from the extracted release");
            }

            if (!hash_equals($meta['sha256'], hash_file('sha256', $path))) {
                throw new RuntimeException("ToolkitService: checksum mismatch for [{$relativePath}] - refusing to use a tampered or corrupted toolkit release");
            }
        }
    }
}
