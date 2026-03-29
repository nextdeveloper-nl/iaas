# XFCE Desktop + Chromium on Ubuntu 24.04 — Setup Guide

**Date:** March 14, 2026  
**Base OS:** Ubuntu 24.04 LTS (Server — minimal install)  
**Purpose:** Golden image for cloud desktop deployment via xrdp

---

## Architecture

```
Customer → Guacamole (HTTPS) → xrdp (3389) → XFCE Desktop
                                                    └── Chromium
```

---

## Why XFCE + xrdp

| | GNOME | XFCE |
|---|---|---|
| RAM usage idle | ~1.5 GB | ~300 MB |
| RDP reliability | Poor | Excellent |
| GPU requirement | Yes | No |
| Boot time | Slow | Fast |
| Remote desktop performance | Poor | Good |
| Cloud VM compatibility | Problematic | Excellent |

---

## Part 1 — Base System Preparation

```bash
sudo apt update && sudo apt upgrade -y
```

---

## Part 2 — Install XFCE Desktop

```bash
sudo apt install -y xfce4 xfce4-goodies
```

---

## Part 3 — Install xrdp

```bash
sudo apt install -y xrdp xorgxrdp mesa-utils libgl1-mesa-dri libglx-mesa0
```

### Add xrdp to ssl-cert group

```bash
sudo adduser xrdp ssl-cert
```

### Configure xrdp to use XFCE

```bash
sudo cat > /etc/xrdp/startwm.sh << 'EOF'
#!/bin/sh
unset DBUS_SESSION_BUS_ADDRESS
unset XDG_RUNTIME_DIR

# Fix snap apps and runtime dir
export XDG_RUNTIME_DIR=/run/user/$(id -u)
if [ ! -d "$XDG_RUNTIME_DIR" ]; then
    mkdir -p "$XDG_RUNTIME_DIR"
    chmod 700 "$XDG_RUNTIME_DIR"
fi

exec startxfce4
EOF
sudo chmod +x /etc/xrdp/startwm.sh
```

### Configure xrdp.ini for performance

```bash
sudo nano /etc/xrdp/xrdp.ini
```

Set these values in `[globals]`:

```ini
[globals]
max_bpp=32
xserverbpp=24
crypt_level=high
bitmap_cache=true
bitmap_compression=true
bulk_compression=true
tcp_nodelay=1
tcp_keepalive=1
```

### Fix gdm3 conflict (Ubuntu 24.04)

```bash
sudo nano /etc/gdm3/custom.conf
```

Add under `[daemon]`:

```ini
[daemon]
WaylandEnable=false
AutomaticLoginEnable=false
```

### Fix IPv6/IPv4 sesman issue

If xrdp and sesman can't communicate, check which address sesman is listening on:

```bash
ss -tlnp | grep 3350
```

If it shows `[::1]:3350` (IPv6), update `xrdp.ini`:

```bash
sudo nano /etc/xrdp/xrdp.ini
```

Add to `[globals]`:

```ini
SesamanAddress=::1
SesamanPort=3350
```

Also update `sesman.ini`:

```bash
sudo nano /etc/xrdp/sesman.ini
```

```ini
ListenAddress=::1
ListenPort=3350

[Security]
AlwaysGroupCheck=false
```

### Enable and start xrdp

```bash
sudo systemctl enable xrdp
sudo systemctl enable xrdp-sesman
sudo systemctl start xrdp-sesman
sleep 2
sudo systemctl start xrdp
```

### Verify

```bash
systemctl status xrdp
ss -tlnp | grep 3389
```

---

## Part 4 — Disable XFCE Compositor

Compositor causes significant performance degradation over RDP. Disable it:

```bash
mkdir -p /home/ubuntu/.config/xfce4/xfconf/xfce-perchannel-xml/

cat > /home/ubuntu/.config/xfce4/xfconf/xfce-perchannel-xml/xfwm4.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<channel name="xfwm4" version="1.0">
  <property name="general" type="empty">
    <property name="use_compositing" type="bool" value="false"/>
    <property name="vblank_mode" type="string" value="off"/>
    <property name="sync_to_vblank" type="bool" value="false"/>
    <property name="use_display_freeze" type="bool" value="false"/>
  </property>
</channel>
EOF

chown -R ubuntu:ubuntu /home/ubuntu/.config
```

---

## Part 5 — Install Chromium

> **Important:** Do NOT use the snap version — it has known issues with xrdp sessions. Use the deb package instead.

```bash
# Add repository
sudo add-apt-repository ppa:xtradeb/apps -y
sudo apt update
sudo apt install -y chromium
```

### Fix Chromium desktop shortcut for xrdp

```bash
sudo sed -i 's|Exec=chromium|Exec=chromium --no-sandbox|g' \
    /usr/share/applications/chromium.desktop
```

### Verify Chromium works

Connect via RDP, open a terminal inside XFCE and run:

```bash
chromium
```

> **Note:** DBus-related error messages in the terminal are harmless and can be ignored. Chromium will launch correctly.

---

## Part 6 — VNC Setup (for Guacamole fallback)

Install TigerVNC alongside xrdp:

```bash
sudo apt install -y tigervnc-standalone-server tigervnc-common dbus-x11 xauth jq
```

### Pre-create global xstartup

```bash
sudo mkdir -p /etc/vnc
sudo cat > /etc/vnc/xstartup << 'EOF'
#!/bin/bash
unset SESSION_MANAGER
unset DBUS_SESSION_BUS_ADDRESS

export DISPLAY=:1
export XDG_SESSION_TYPE=x11
export XDG_CURRENT_DESKTOP=XFCE
export LIBGL_ALWAYS_SOFTWARE=1

eval $(dbus-launch --sh-syntax)
export DBUS_SESSION_BUS_ADDRESS

exec startxfce4
EOF
sudo chmod +x /etc/vnc/xstartup
```

### Pre-install VNC systemd service

```bash
cat > /etc/systemd/system/vncserver@.service << 'EOF'
[Unit]
Description=TigerVNC server for %i
After=network.target

[Service]
Type=forking
User=%i
PIDFile=/home/%i/.vnc/%H:1.pid
ExecStartPre=/bin/bash -c '/usr/bin/vncserver -kill :1 > /dev/null 2>&1 || true'
ExecStart=/usr/bin/vncserver :1 \
    -geometry 1920x1080 \
    -depth 24 \
    -localhost yes \
    -SecurityTypes VncAuth
ExecStop=/bin/bash -c '/usr/bin/vncserver -kill :1 || true'
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl daemon-reload
```

---

## Part 7 — First Boot Script

Located at `/root/startup.sh` — configures VNC password from metadata on first boot, starts VNC on every boot.

```bash
#!/bin/bash
# /root/startup.sh
# Usage: /root/startup.sh <metadata_file>

LOG="/var/log/startup.log"
exec > >(tee -a $LOG) 2>&1
echo "=== Startup script running at $(date) ==="

METADATA_FILE=$1
FIRST_BOOT_FLAG="/var/lib/startup-done"

# Validate metadata file
if [ -z "$METADATA_FILE" ] || [ ! -f "$METADATA_FILE" ]; then
    echo "ERROR: Metadata file not found: $METADATA_FILE"
    exit 1
fi

# Parse username — needed on every boot
DESKTOP_USER=$(jq -r '.username' $METADATA_FILE)

# ─── RUNS EVERY BOOT ─────────────────────────────────────────────────────────

if [ -f "$FIRST_BOOT_FLAG" ]; then
    echo "Already initialized — starting VNC service only."
    systemctl start vncserver@$DESKTOP_USER
    echo "=== Done at $(date) ==="
    exit 0
fi

# ─── RUNS FIRST BOOT ONLY ────────────────────────────────────────────────────

echo "First boot — configuring VNC..."

VNC_PASSWORD=$(jq -r '.tokens.vnc' $METADATA_FILE)
DESKTOP_HOME=$(getent passwd $DESKTOP_USER | cut -d: -f6)

if [ -z "$VNC_PASSWORD" ] || [ "$VNC_PASSWORD" == "null" ]; then
    echo "ERROR: VNC token not found in metadata."
    exit 1
fi

# Set up .vnc directory
mkdir -p $DESKTOP_HOME/.vnc

# Copy pre-baked xstartup
cp /etc/vnc/xstartup $DESKTOP_HOME/.vnc/xstartup
chown -R $DESKTOP_USER:$DESKTOP_USER $DESKTOP_HOME/.vnc

# Set VNC password
echo "$VNC_PASSWORD" | sudo -u $DESKTOP_USER vncpasswd -f > $DESKTOP_HOME/.vnc/passwd
chmod 600 $DESKTOP_HOME/.vnc/passwd
chown $DESKTOP_USER:$DESKTOP_USER $DESKTOP_HOME/.vnc/passwd
unset VNC_PASSWORD

# Enable and start VNC
systemctl enable vncserver@$DESKTOP_USER
systemctl start vncserver@$DESKTOP_USER

# Verify VNC started
sleep 3
if systemctl is-active --quiet vncserver@$DESKTOP_USER; then
    echo "VNC started successfully on port 5901."
else
    echo "ERROR: VNC failed to start."
    journalctl -u vncserver@$DESKTOP_USER --no-pager -n 20
    exit 1
fi

# Mark as done — must be last step
touch $FIRST_BOOT_FLAG
echo "=== First boot complete at $(date) ==="
```

```bash
sudo chmod +x /root/startup.sh
```

---

## Part 8 — Image Cleanup Before Export

Run this before exporting the golden image:

```bash
# Clear cloud-init state
sudo cloud-init clean --logs

# Clear machine ID
sudo truncate -s 0 /etc/machine-id
sudo rm -f /var/lib/dbus/machine-id
sudo ln -s /etc/machine-id /var/lib/dbus/machine-id

# Clear SSH host keys
sudo rm -f /etc/ssh/ssh_host_*

# Clear VNC passwords
rm -f ~/.vnc/passwd

# Clear bash history
sudo truncate -s 0 /root/.bash_history
history -c

# Clear logs
sudo truncate -s 0 /var/log/syslog
sudo truncate -s 0 /var/log/auth.log
sudo journalctl --rotate --vacuum-time=1s

# Clear apt cache
sudo apt autoremove -y
sudo apt clean
sudo rm -rf /var/lib/apt/lists/*

echo "Image ready for export."
```

---

## Connecting via RDP

### xfreerdp (Linux/Mac)

```bash
xfreerdp /v:VM_IP:3389 \
    /u:ubuntu \
    /p:PASSWORD \
    /cert-ignore \
    +clipboard \
    /dynamic-resolution \
    /w:1920 /h:1080 \
    /bpp:32
```

### Remmina

| Field | Value |
|---|---|
| Protocol | `RDP` |
| Server | `VM_IP:3389` |
| Username | `ubuntu` |
| Password | `OS password` |
| Color depth | `True color (32-bit)` |
| Security | `RDP` (not Negotiate) |
| Ignore certificate | `Yes` |

### Windows RDP Client

```
Computer: VM_IP:3389
Username: ubuntu
Password: OS password
```

---

## Guacamole RDP Connection Settings

| Field | Value |
|---|---|
| Protocol | `RDP` |
| Hostname | `127.0.0.1` |
| Port | `13389` (via SSH tunnel) |
| Username | `ubuntu` |
| Password | OS password from metadata |
| Color depth | `True color (32-bit)` |
| Ignore server certificate | `true` |
| Enable wallpaper | `false` |
| Enable font smoothing | `true` |
| Enable full window drag | `false` |
| Enable desktop composition | `false` |
| Network connection type | `LAN` |

---

## Troubleshooting

### xrdp and sesman not communicating

```bash
# Check which address sesman is listening on
ss -tlnp | grep 3350

# Test connectivity
nc -zv ::1 3350
nc -zv 127.0.0.1 3350
```

### Black screen after RDP login

```bash
# Check if XFCE session started
ps aux | grep -E "xfce|xfwm|xfdesktop"

# Check session log
cat /home/ubuntu/.xsession-errors
cat /home/ubuntu/.xorgxrdp.*.log 2>/dev/null | tail -30
```

### Chromium won't launch

```bash
# Always launch from within RDP session terminal
# Never from SSH terminal

# If needed set display manually
export DISPLAY=:10
export XAUTHORITY=/home/ubuntu/.Xauthority
xhost +local:
chromium --no-sandbox
```

### VNC service fails to start

```bash
# Check service status
systemctl status vncserver@ubuntu

# Remove stale lock files
rm -f /tmp/.X1-lock
rm -f /tmp/.X11-unix/X1

# Restart
systemctl restart vncserver@ubuntu
```

### Reset first boot

```bash
# Force full setup to run again on next boot
sudo rm /var/lib/startup-done
sudo reboot
```

---

## Useful Diagnostic Commands

```bash
# Check all remote desktop services
systemctl status xrdp xrdp-sesman vncserver@ubuntu

# Check listening ports
ss -tlnp | grep -E "3389|5901|3350"

# Check xrdp logs
tail -f /var/log/xrdp.log
tail -f /var/log/xrdp-sesman.log

# Check startup script log
tail -f /var/log/startup.log

# Check active display sessions
ls -la /tmp/.X*
ps aux | grep -E "Xorg|Xtigervnc|xrdp"
```

---

## Package Summary

| Package | Purpose |
|---|---|
| `xfce4` | Desktop environment |
| `xfce4-goodies` | XFCE extras and plugins |
| `xrdp` | RDP server |
| `xorgxrdp` | Xorg backend for xrdp |
| `mesa-utils` | Graphics utilities |
| `libgl1-mesa-dri` | Software rendering |
| `libglx-mesa0` | OpenGL support |
| `tigervnc-standalone-server` | VNC server |
| `tigervnc-common` | VNC utilities |
| `dbus-x11` | DBus X11 support |
| `xauth` | X11 authentication |
| `jq` | JSON parsing for startup script |
| `chromium` | Web browser (deb via xtradeb/apps) |
