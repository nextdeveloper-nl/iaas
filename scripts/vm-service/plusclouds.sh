#!/bin/bash
# This is the bootup script which is running when the linux servers booted up to configure the machine
#
# - This file should be deployed under /usr/local/bin folder with plusclouds.sh filename. Also you should make chmod +x
#   to the file.
# - Make sure that the plusclouds.service is also deployed on the VM.
#
# This runs on every boot (Type=oneshot, WantedBy=multi-user.target), so it
# must be safe to re-run even if a previous run left /tmp/pc-config behind
# (crash, forced power-cycle, etc). set -e makes real failures show up as a
# failed unit in systemctl/journalctl instead of silently exiting 0.
set -e

rm -rf /tmp/pc-config
mkdir -p /tmp/pc-config

mkdir -p /mnt/tmp-configuration
mount /dev/sr0 /mnt/tmp-configuration
# trailing /. copies the directory's contents into /tmp/pc-config regardless
# of whether it already existed - cp without it nests one level deeper
# (/tmp/pc-config/tmp-configuration/...) when the destination pre-exists.
cp /mnt/tmp-configuration/. /tmp/pc-config -R
umount /mnt/tmp-configuration
sleep 1
rm -rf /mnt/tmp-configuration

cd /tmp/pc-config
ansible-playbook apply-configuration.yml -i localhost, -c local

rm -rf /tmp/pc-config
