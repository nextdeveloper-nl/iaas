#Requires -RunAsAdministrator
param(
    [Parameter(Mandatory)][string]$Username,
    [Parameter(Mandatory)][array]$SshKeys
)

function Apply-KeysForUser {
    param(
        [string]$User,
        [string]$SshDir
    )

    if (-not (Test-Path $SshDir)) {
        New-Item -ItemType Directory -Path $SshDir -Force | Out-Null
    }

    $authorizedKeysFile = Join-Path $SshDir 'authorized_keys'

    if (-not (Test-Path $authorizedKeysFile)) {
        New-Item -ItemType File -Path $authorizedKeysFile -Force | Out-Null
    }

    $existingKeys = Get-Content $authorizedKeysFile -ErrorAction SilentlyContinue

    foreach ($key in $SshKeys) {
        if (-not $key.public_key) { continue }

        $publicKey = $key.public_key.Trim()

        if ($existingKeys -contains $publicKey) {
            Write-Host "Key '$($key.name)' already present for '$User', skipping."
        } else {
            Add-Content -Path $authorizedKeysFile -Value $publicKey
            Write-Host "Added key '$($key.name)' for '$User'."
        }
    }

    # Fix permissions — OpenSSH on Windows requires restricted ACL on authorized_keys
    icacls $authorizedKeysFile /inheritance:r /grant "${User}:(R)" /grant "SYSTEM:(R)" | Out-Null
}

# Apply for the VM user
$userProfile = (Get-LocalUser -Name $Username -ErrorAction SilentlyContinue) |
    ForEach-Object { (Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows NT\CurrentVersion\ProfileList\*" |
        Where-Object { $_.PSChildName -match (New-Object System.Security.Principal.NTAccount($Username)).Translate([System.Security.Principal.SecurityIdentifier]).Value }).ProfileImagePath }

if ($userProfile) {
    $userSshDir = Join-Path $userProfile '.ssh'
    Apply-KeysForUser -User $Username -SshDir $userSshDir
} else {
    Write-Warning "Could not resolve profile path for '$Username', skipping user SSH keys."
}

# Apply for Administrator
$adminSshDir = Join-Path $env:ProgramData 'ssh'
Apply-KeysForUser -User 'SYSTEM' -SshDir $adminSshDir
