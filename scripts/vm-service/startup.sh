#!/bin/bash

set -e  # Exit on error

# Variables
REPO_URL="https://github.com/plusclouds/vm-services"
CLONE_DIR="/tmp/vm-services"
INVENTORY="localhost,"     # Adjust if you're targeting other hosts
PLAYBOOK="apply-configuration.yml"        # Replace with your actual playbook file

# Install Ansible if missing
if ! command -v ansible-playbook &> /dev/null; then
    echo "[INFO] Ansible not found. Installing..."
    sudo apt update && sudo apt install -y ansible
fi

# Clean previous clone
if [ -d "$CLONE_DIR" ]; then
    echo "[INFO] Removing old repo at $CLONE_DIR"
    rm -rf "$CLONE_DIR"
fi

# Clone Git repo
echo "[INFO] Cloning $REPO_URL"
git clone "$REPO_URL" "$CLONE_DIR"

cd "$CLONE_DIR"

# Install roles if requirements.yml exists and is not empty
if [ -s requirements.yml ]; then
    echo "[INFO] Installing roles from requirements.yml"
    ansible-galaxy install -r requirements.yml
else
    echo "[INFO] Skipping requirements.yml (not found or empty)"
fi

# Run playbook
if [ -f "$PLAYBOOK" ]; then
    echo "[INFO] Running playbook: $PLAYBOOK"
    ansible-playbook -i "$INVENTORY" "$PLAYBOOK" --connection=local
else
    echo "[ERROR] Playbook '$PLAYBOOK' not found in $CLONE_DIR"
    exit 1
fi

echo "[INFO] Ansible run complete"
