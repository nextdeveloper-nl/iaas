#Requires -RunAsAdministrator
param(
    [Parameter(Mandatory)][PSCustomObject]$EnvVars
)

foreach ($prop in $EnvVars.PSObject.Properties) {
    [System.Environment]::SetEnvironmentVariable($prop.Name, $prop.Value, 'Machine')
    Write-Host "Set environment variable: $($prop.Name)"
}

Write-Host "Environment variables applied."
