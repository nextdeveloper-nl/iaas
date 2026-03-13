#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Registers a startup task that detects the mounted config ISO and runs apply-configuration.ps1 from it.
.DESCRIPTION
    The config ISO is mounted as a CD-ROM drive. Its drive letter may vary, so the startup
    task runs a small inline script that scans all CD-ROM drives for pc-meta-data.json
    and executes apply-configuration.ps1 from the same location.
#>

$TaskName = 'PlusCloudsConfigure'

# Inline script embedded in the task action — discovers the ISO drive at runtime
$InlineScript = @'
$cdDrive = Get-WmiObject Win32_LogicalDisk |
    Where-Object { $_.DriveType -eq 5 } |
    ForEach-Object { $_.DeviceID } |
    Where-Object { Test-Path (Join-Path $_ 'pc-meta-data.json') } |
    Select-Object -First 1

if (-not $cdDrive) {
    Write-EventLog -LogName Application -Source 'PlusCloudsConfigure' -EntryType Error -EventId 1 `
        -Message 'PlusCloudsConfigure: Could not find config ISO. No CD-ROM drive contains pc-meta-data.json.'
    exit 1
}

$script = Join-Path $cdDrive 'apply-configuration.ps1'

if (-not (Test-Path $script)) {
    Write-EventLog -LogName Application -Source 'PlusCloudsConfigure' -EntryType Error -EventId 1 `
        -Message "PlusCloudsConfigure: apply-configuration.ps1 not found on $cdDrive"
    exit 1
}

& powershell.exe -NonInteractive -ExecutionPolicy Bypass -File $script
'@

# Register event source so the inline script can log errors
if (-not [System.Diagnostics.EventLog]::SourceExists('PlusCloudsConfigure')) {
    New-EventLog -LogName Application -Source 'PlusCloudsConfigure'
}

$action = New-ScheduledTaskAction `
    -Execute 'powershell.exe' `
    -Argument "-NonInteractive -ExecutionPolicy Bypass -Command `"$InlineScript`""

$trigger = New-ScheduledTaskTrigger -AtStartup

$settings = New-ScheduledTaskSettingsSet `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 10) `
    -RestartCount 0

$principal = New-ScheduledTaskPrincipal `
    -UserId 'SYSTEM' `
    -LogonType ServiceAccount `
    -RunLevel Highest

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Force

Write-Host "Task '$TaskName' registered. On next startup it will scan CD-ROM drives for the config ISO and run apply-configuration.ps1."
