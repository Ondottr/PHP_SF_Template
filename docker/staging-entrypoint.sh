#!/bin/sh
set -e

/usr/sbin/sshd

php bin/console assets:install public --quiet
php bin/console cache:clear --quiet

exec php -S 0.0.0.0:8000 -t public
