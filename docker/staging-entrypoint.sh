#!/bin/sh
set -e

# Fetch SSH public keys from GitHub so repo maintainers can access the container
GITHUB_USER="${STAGING_SSH_GITHUB_USER:-ondottr}"
curl -sf "https://github.com/${GITHUB_USER}.keys" >> /root/.ssh/authorized_keys 2>/dev/null || true
chmod 600 /root/.ssh/authorized_keys

/usr/sbin/sshd

php bin/console assets:install public --quiet
php bin/console cache:clear --quiet

exec php -S 0.0.0.0:8000 -t public
