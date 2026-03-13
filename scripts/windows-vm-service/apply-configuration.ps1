#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Reads pc-meta-data.json and applies configuration to the Windows VM.
.DESCRIPTION
    Mirrors the Linux apply-configuration.yml behaviour.
    Run at first boot via Task Scheduler (see register-startup-task.ps1).
#>

$ErrorActionPreference = 'Stop'
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$MetaDataFile = Join-Path $ScriptDir 'pc-meta-data.json'

if (-not (Test-Path $MetaDataFile)) {
    Write-Error "pc-meta-data.json not found at $MetaDataFile"
    exit 1
}

$metadata = Get-Content $MetaDataFile -Raw | ConvertFrom-Json

Write-Host "==> Loaded metadata for: $($metadata.hostname)"

# 1. Hostname
if ($metadata.hostname) {
    & "$ScriptDir\change-hostname.ps1" -Hostname $metadata.hostname
}

# 2. Password
if ($metadata.username -and $metadata.password) {
    & "$ScriptDir\change-password.ps1" -Username $metadata.username -Password $metadata.password
}

# 3. Environment variables
if ($metadata.env_vars -and $metadata.env_vars.PSObject.Properties.Count -gt 0) {
    & "$ScriptDir\apply-env-vars.ps1" -EnvVars $metadata.env_vars
}

# 4. SSH public keys
if ($metadata.ssh_keys -and $metadata.ssh_keys.Count -gt 0) {
    & "$ScriptDir\apply-ssh-keys.ps1" -Username $metadata.username -SshKeys $metadata.ssh_keys
}

Write-Host "==> Configuration applied successfully."
