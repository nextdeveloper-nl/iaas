#!/bin/bash
set -e  # Exit on error
# Clear bash history
history -c
cat /dev/null > ~/.bash_history

# Remove SSH host keys (regenerated on first boot)
rm -f /etc/ssh/ssh_host_*

# Clear machine-id (important for unique instances)
truncate -s 0 /etc/machine-id

# Clear temp files and logs
rm -rf /tmp/* /var/tmp/*
journalctl --rotate --vacuum-time=1s
