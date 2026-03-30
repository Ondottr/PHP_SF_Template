#!/bin/sh
set -e

php bin/console assets:install public --quiet
php bin/console cache:clear --quiet

exec php -S 0.0.0.0:8000 -t public
