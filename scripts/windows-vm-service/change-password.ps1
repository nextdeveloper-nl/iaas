#Requires -RunAsAdministrator
param(
    [Parameter(Mandatory)][string]$Username,
    [Parameter(Mandatory)][string]$Password
)

$securePassword = ConvertTo-SecureString $Password -AsPlainText -Force

$localUser = Get-LocalUser -Name $Username -ErrorAction SilentlyContinue

if ($localUser) {
    Set-LocalUser -Name $Username -Password $securePassword
    Write-Host "Password updated for user '$Username'."
} else {
    New-LocalUser -Name $Username -Password $securePassword -PasswordNeverExpires -UserMayNotChangePassword
    Add-LocalGroupMember -Group 'Administrators' -Member $Username
    Write-Host "User '$Username' created and added to Administrators."
}
