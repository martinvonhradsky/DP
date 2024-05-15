#!/bin/bash

# Exit if one of the commands exits non-zero
set -e

# Copy available_tests.txt to /data when container starts
cp /var/www/html/module/engine/available_tests.txt /data
mkdir -p /root/.ssh
echo $WEB_PUBLIC_KEY > /root/.ssh/id_ed25519.pub
chmod 644 /root/.ssh/id_ed25519.pub
echo $WEB_PRIVATE_KEY | sed 's/%/\\n/g' > /root/.ssh/id_ed25519
chmod 600 /root/.ssh/id_ed25519
touch /data-known_hosts/known_hosts
ln -fs /data-known_hosts/known_hosts /root/.ssh/known_hosts

php -S 0.0.0.0:80 -t /var/www/html/module