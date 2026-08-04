Straightforward process — CloudLinux is CentOS/RHEL based, so standard Linux user management applies:

## 1. Create the User

```bash
adduser cloudlinux
```

Set a password:
```bash
passwd cloudlinux
```

## 2. Add to sudoers

**Option A — Add to `wheel` group (recommended):**
```bash
usermod -aG wheel cloudlinux
```

On RHEL/CentOS-based systems, the `wheel` group already has sudo access by default. Verify it's enabled:
```bash
grep wheel /etc/sudoers
# Should see: %wheel  ALL=(ALL)  ALL
```

**Option B — Add directly to sudoers file:**
```bash
visudo
```

Then add this line:
```
cloudlinux ALL=(ALL) NOPASSWD:ALL
```

Or with password prompt (more secure):
```
cloudlinux ALL=(ALL) ALL
```

**Option C — Drop a file in `/etc/sudoers.d/` (cleanest for management):**
```bash
echo "cloudlinux ALL=(ALL) ALL" > /etc/sudoers.d/cloudlinux
chmod 440 /etc/sudoers.d/cloudlinux
```

---

## 4. Disable Root SSH Login

Once you've confirmed the new user can sudo, lock down root:

```bash
vi /etc/ssh/sshd_config
```

Set:
```
PermitRootLogin no
PasswordAuthentication no   # if using key-only
```

Restart SSH:
```bash
systemctl restart sshd
```

---

## 5. Exclude from CageFS (important on CloudLinux)

If CageFS is active, system/admin users should be excluded so they're not jailed:

```bash
cagefsctl --exclude cloudlinux
cagefsctl --update
```

Otherwise your sudo user might hit restricted filesystem views unexpectedly.

---

**Quick recap flow:**
```
adduser cloudlinux → usermod -aG wheel cloudlinux → setup SSH key → disable root SSH → cagefsctl --exclude cloudlinux
```
