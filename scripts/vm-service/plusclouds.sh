#!/bin/bash
# This is the bootup script which is running when the linux servers booted up to configure the machine
#
# - This file should be deployed under /usr/local/bin folder with plusclouds.sh filename. Also you should make chmod +x
#   to the file.
# - Make sure that the plusclouds.service is also deployed on the VM.
mkdir /mnt/tmp-configuration
mount /dev/sr0 /mnt/tmp-configuration
cp /mnt/tmp-configuration /tmp/pc-config -R
umount /mnt/tmp-configuration
rm /mnt/tmp-configuration -R
cd /tmp/pc-config
ansible-playbook apply-configuration.yml -i localhost, -c local
rm /tmp/pc-config -R
