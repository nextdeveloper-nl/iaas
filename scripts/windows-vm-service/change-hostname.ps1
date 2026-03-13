#Requires -RunAsAdministrator
param(
    [Parameter(Mandatory)][string]$Hostname
)

$current = $env:COMPUTERNAME

if ($current -eq $Hostname) {
    Write-Host "Hostname already set to '$Hostname', skipping."
    return
}

Write-Host "Changing hostname from '$current' to '$Hostname'..."
Rename-Computer -NewName $Hostname -Force
Write-Host "Hostname changed. A reboot is required to take effect."
