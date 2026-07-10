#!/bin/bash
set -e  # Exit on error

REPO_RAW_BASE="https://raw.githubusercontent.com/nextdeveloper-nl/iaas/refs/heads/master/scripts/vm-service"
SCRIPT_PATH="/usr/local/bin/plusclouds.sh"
SERVICE_PATH="/etc/systemd/system/plusclouds.service"

echo "[INFO] Downloading plusclouds.sh to $SCRIPT_PATH"
sudo curl -fsSL "$REPO_RAW_BASE/plusclouds.sh" -o "$SCRIPT_PATH"
sudo chmod +x "$SCRIPT_PATH"

echo "[INFO] Downloading plusclouds.service to $SERVICE_PATH"
sudo curl -fsSL "$REPO_RAW_BASE/plusclouds.service" -o "$SERVICE_PATH"

echo "[INFO] Reloading systemd and enabling plusclouds.service"
sudo systemctl daemon-reload
sudo systemctl enable plusclouds.service
sudo systemctl start plusclouds.service

echo "[INFO] plusclouds.service installed and started"

# --- Template sysprep ---------------------------------------------------
# Strips per-instance identity so a clone of this VM doesn't inherit it.
# Destructive and irreversible: only run this on a VM you are about to
# halt and convert to a template (NextDeveloper\IAAS ConvertToTemplate),
# never on a live/already-deployed VM, since it removes SSH host keys
# and wipes logs/history.
echo "[INFO] Sysprep: clearing instance identity before templating"

# Clear bash history
history -c
cat /dev/null > ~/.bash_history

# Remove SSH host keys (regenerated on first boot)
sudo rm -f /etc/ssh/ssh_host_*

# Clear machine-id (important for unique instances)
sudo truncate -s 0 /etc/machine-id

# Clear temp files and logs
sudo rm -rf /tmp/* /var/tmp/*
sudo journalctl --rotate --vacuum-time=1s

echo "[INFO] Sysprep complete"
