#!/usr/bin/env bash

# install.sh — Set up an existing PHP_SF project on a new machine
#
# Use this when cloning a project that has already been initialized (doctrine.yaml
# and entity/repository directories are already configured in the repository).
#
# For initializing a brand-new project from the template, use init.sh instead.

set -e

if [ ! -f "composer.json" ]; then
  echo "This script must be run from the root of the project"
  exit 1
fi

if ! [ -x "$(command -v composer)" ]; then
  echo "Composer is not installed"
  exit 1
fi

# region Configure .env (only on first run — when .env doesn't exist yet)

set_db_credentials() {
  local em_name="$1"
  local em_upper="${em_name^^}"

  echo ""
  echo "Configure '$em_name' connection"

  # Read driver from doctrine.yaml to determine default port
  local current_driver
  current_driver=$(php -r "
    require_once 'vendor/autoload.php';
    use Symfony\Component\Yaml\Yaml;
    \$yaml = Yaml::parseFile('config/packages/doctrine.yaml');
    echo \$yaml['doctrine']['dbal']['connections']['${em_name}']['driver'] ?? '';
  " 2>/dev/null || echo '')

  declare -A driver_ports=( [pdo_pgsql]=5432 [pdo_mysql]=3306 [pdo_sqlite]=0 )
  local default_port=${driver_ports[$current_driver]:-3306}

  # Read current defaults from .env
  local current_dbname current_version current_dbname_test
  current_dbname=$(php -r "
    foreach (file('.env', FILE_IGNORE_NEW_LINES) as \$l) {
      if (preg_match('/^#?DATABASE_${em_upper}_DBNAME=(.*)/', \$l, \$m)) { echo trim(\$m[1]); break; }
    }
  ")
  current_version=$(php -r "
    foreach (file('.env', FILE_IGNORE_NEW_LINES) as \$l) {
      if (preg_match('/^#?DATABASE_${em_upper}_VERSION=(.*)/', \$l, \$m)) { echo trim(\$m[1]); break; }
    }
  ")
  current_dbname_test=$(php -r "
    foreach (file('.env', FILE_IGNORE_NEW_LINES) as \$l) {
      if (preg_match('/^#?DATABASE_${em_upper}_DBNAME_TEST=(.*)/', \$l, \$m)) { echo trim(\$m[1]); break; }
    }
  ")

  read -r -p "Database host (default 127.0.0.1): " database_host
  [ -z "$database_host" ] && database_host="127.0.0.1"

  read -r -p "Database port (default $default_port): " database_port
  [ -z "$database_port" ] && database_port=$default_port

  read -r -p "Database user: " database_user
  [ -z "$database_user" ] && { echo "Database user cannot be empty!"; exit 1; }

  read -r -p "Database password: " database_password
  [ -z "$database_password" ] && { echo "Database password cannot be empty!"; exit 1; }

  read -r -p "Database name (default ${current_dbname:-$em_name}): " database_dbname
  [ -z "$database_dbname" ] && database_dbname="${current_dbname:-$em_name}"

  read -r -p "Database version (default ${current_version:-}): " database_version
  [ -z "$database_version" ] && database_version="${current_version:-}"

  local default_test="${current_dbname_test:-${database_dbname}_test}"
  read -r -p "Test database name (default $default_test): " database_dbname_test
  [ -z "$database_dbname_test" ] && database_dbname_test="$default_test"

  # Write HOST, PORT, USER, PASSWORD, DBNAME, VERSION, DBNAME_TEST
  php -r "
    \$env = file_get_contents('.env');
    \$env = preg_replace('/^#?DATABASE_${em_upper}_HOST=.*/m',       'DATABASE_${em_upper}_HOST=${database_host}', \$env);
    \$env = preg_replace('/^#?DATABASE_${em_upper}_PORT=.*/m',       'DATABASE_${em_upper}_PORT=${database_port}', \$env);
    \$env = preg_replace('/^#?DATABASE_${em_upper}_USER=.*/m',       'DATABASE_${em_upper}_USER=${database_user}', \$env);
    \$env = preg_replace('/^#?DATABASE_${em_upper}_PASSWORD=.*/m',   'DATABASE_${em_upper}_PASSWORD=${database_password}', \$env);
    \$env = preg_replace('/^#?DATABASE_${em_upper}_DBNAME=.*/m',     'DATABASE_${em_upper}_DBNAME=${database_dbname}', \$env);
    \$env = preg_replace('/^#?DATABASE_${em_upper}_VERSION=.*/m',    'DATABASE_${em_upper}_VERSION=${database_version}', \$env);
    \$env = preg_replace('/^#?DATABASE_${em_upper}_DBNAME_TEST=.*/m','DATABASE_${em_upper}_DBNAME_TEST=${database_dbname_test}', \$env);
    file_put_contents('.env', \$env);
  "
  echo "'$em_name' credentials saved"
}

if [ ! -f ".env" ]; then
  cp .env.example .env

  # Configure every DATABASE_*_USER that is still commented out
  db_prompt="Add a database connection? [Y/n]: "
  while IFS= read -r line; do
    if [[ "$line" =~ ^#DATABASE_([A-Z0-9]+)_USER= ]]; then
      em_name="${BASH_REMATCH[1],,}"
      set_db_credentials "$em_name"
      db_prompt="Add another database connection? [Y/n]: "
    fi
  done < .env

  echo ""
  echo "Set application environment prod|dev|test (default dev):"
  read -r application_environment
  [ -z "$application_environment" ] && application_environment="dev"
  php -r "file_put_contents('.env', preg_replace('/^#APP_ENV=.*/m', 'APP_ENV=$application_environment', file_get_contents('.env')));"

  echo "Enable debug mode? y|N"
  read -r debug_mode
  if [ "$debug_mode" = "y" ] || [ "$debug_mode" = "Y" ]; then debug_mode="true"; else debug_mode="false"; fi
  php -r "file_put_contents('.env', preg_replace('/^#APP_DEBUG=.*/m', 'APP_DEBUG=$debug_mode', file_get_contents('.env')));"

  echo ""
  echo "Set Redis credentials"
  read -r -p "Redis host (default localhost): " redis_host;  [ -z "$redis_host" ]  && redis_host="localhost"
  read -r -p "Redis port (default 6379): " redis_port;       [ -z "$redis_port" ]  && redis_port="6379"
  read -r -p "Redis database (default 0): " redis_db;        [ -z "$redis_db" ]    && redis_db="0"
  php -r "file_put_contents('.env', preg_replace('/^#REDIS_CACHE_URL=.*/m', 'REDIS_CACHE_URL=redis://$redis_host:$redis_port/$redis_db', file_get_contents('.env')));"

  echo ""
  echo "Set Memcached credentials"
  read -r -p "Memcached host (default localhost): " memcached_host; [ -z "$memcached_host" ] && memcached_host="localhost"
  read -r -p "Memcached port (default 11211): " memcached_port;     [ -z "$memcached_port" ] && memcached_port="11211"
  php -r "file_put_contents('.env', preg_replace('/^#MEMCACHED_SERVER=.*/m', 'MEMCACHED_SERVER=$memcached_host', file_get_contents('.env')));"
  php -r "file_put_contents('.env', preg_replace('/^#MEMCACHED_PORT=.*/m', 'MEMCACHED_PORT=$memcached_port', file_get_contents('.env')));"

  echo ""
  echo "Set server prefix (default server):"
  read -r server_prefix; [ -z "$server_prefix" ] && server_prefix="server"
  php -r "file_put_contents('.env', preg_replace('/^#SERVER_PREFIX=.*/m', 'SERVER_PREFIX=$server_prefix', file_get_contents('.env')));"

  echo ""
  echo "Set admin user credentials"
  read -r -p "Admin email (default adminemail@example.com): " admin_email; [ -z "$admin_email" ] && admin_email="adminemail@example.com"
  read -r -p "Admin password (default admin_password): " admin_password;   [ -z "$admin_password" ] && admin_password="admin_password"
  php -r "file_put_contents('.env', preg_replace('/^#ADMIN_EMAIL=.*/m', 'ADMIN_EMAIL=$admin_email', file_get_contents('.env')));"
  php -r "file_put_contents('.env', preg_replace('/^#ADMIN_PASSWORD=.*/m', 'ADMIN_PASSWORD=$admin_password', file_get_contents('.env')));"
fi

# endregion

# region Configure config/constants.php

if [ ! -f "config/constants.php" ]; then
  cp config/constants.example.php config/constants.php
fi

if grep -q "^#const SERVER_IP" config/constants.php; then
  echo "Set server IP (default 127.0.0.1):"
  read -r server_ip; [ -z "$server_ip" ] && server_ip="127.0.0.1"
  php -r "file_put_contents('config/constants.php', preg_replace('/^#const SERVER_IP.*/m', 'const SERVER_IP = \"$server_ip\";', file_get_contents('config/constants.php')));"
fi

if grep -q "^#const DEV_MODE" config/constants.php; then
  echo "Enable templates cache? Y|n"
  read -r templates_cache_enabled
  if [ "$templates_cache_enabled" = "n" ] || [ "$templates_cache_enabled" = "N" ]; then templates_cache_enabled="false"; else templates_cache_enabled="true"; fi
  php -r "file_put_contents('config/constants.php', preg_replace('/^#const TEMPLATES_CACHE_ENABLED.*/m', 'const TEMPLATES_CACHE_ENABLED = $templates_cache_enabled;', file_get_contents('config/constants.php')));"

  echo "Enable dev mode? y|N"
  read -r dev_mode
  if [ "$dev_mode" = "y" ] || [ "$dev_mode" = "Y" ]; then dev_mode="true"; else dev_mode="false"; fi
  php -r "file_put_contents('config/constants.php', preg_replace('/^#const DEV_MODE.*/m', 'const DEV_MODE = $dev_mode;', file_get_contents('config/constants.php')));"
fi

if grep -q "^#const APPLICATION_NAME" config/constants.php; then
  echo "Set application name (default Platform):"
  read -r application_name; [ -z "$application_name" ] && application_name="Platform"
  php -r "file_put_contents('config/constants.php', preg_replace('/^#const APPLICATION_NAME.*/m', 'const APPLICATION_NAME = \"$application_name\";', file_get_contents('config/constants.php')));"
fi

if grep -q "^//define('LANGUAGES_LIST" config/constants.php; then
  echo "You must define or uncomment the LANGUAGES_LIST constant in config/constants.php manually!"
  exit 1
fi

# endregion

# region Install dependencies

if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install --ignore-platform-reqs --no-scripts
fi

if [ ! -d "node_modules" ]; then
  echo "Installing npm dependencies..."
  yarn install
  cd public/CKEditor || cd .
  yarn install
  cd ../..

  echo "Building assets..."
  yarn build
  cd public/CKEditor || cd .
  yarn build
  cd ../..

  php bin/console assets:install
fi

# endregion

# region Build Codeception actors

echo "Building Codeception actor classes..."
vendor/bin/codecept build --quiet

# endregion

# region Create schemas and load fixtures

configured_ems=()
while IFS= read -r line; do
  if [[ "$line" =~ ^DATABASE_([A-Z0-9]+)_USER= ]]; then
    em_name="${BASH_REMATCH[1],,}"
    [ "$em_name" != "dummy" ] && configured_ems+=("$em_name")
  fi
done < .env

if [ ${#configured_ems[@]} -gt 0 ]; then
  echo ""
  echo "Configured entity managers: ${configured_ems[*]}"
  echo "Create database schemas? [Y/n]"
  read -r answer
  if [ -z "$answer" ] || [ "$answer" = "y" ] || [ "$answer" = "Y" ]; then
    for em in "${configured_ems[@]}"; do
      echo "Creating schema for '$em'..."
      php bin/console doctrine:database:create --if-not-exists --connection="$em"
      php bin/console doctrine:schema:drop -f --em="$em"
      php bin/console doctrine:schema:create --em="$em"
    done
    echo "Schemas created"

    echo "Run fixtures? Y|n"
    read -r answer
    if [ -z "$answer" ] || [ "$answer" = "y" ] || [ "$answer" = "Y" ]; then
      for em in "${configured_ems[@]}"; do
        echo "Loading fixtures for '$em'..."
        php bin/console doctrine:fixtures:load --no-interaction --em="$em"
      done
      echo "Fixtures loaded"
    fi
  fi
fi

# endregion

chmod +x run.sh

echo ""
echo "Installation complete. Run the application with: ./run.sh"
