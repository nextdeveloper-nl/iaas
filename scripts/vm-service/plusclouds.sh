#!/bin/bash
# This is the bootup script which is running when the linux servers booted up to configurat the machine
mkdir /mnt/tmp-configuration
mount /dev/sr0 /mnt/tmp-configuration
cp /mnt/tmp-configuration /tmp/pc-config -R
umount /mnt/tmp-configuration
rm /mnt/tmp-configuration -R
cd /tmp/pc-config
ansible-playbook apply-configuration.yml -i localhost, -c local
rm /tmp/pc-config -R
