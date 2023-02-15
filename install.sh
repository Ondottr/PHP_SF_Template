#!/usr/bin/env bash

# This script is used to install and configure the PHP_SF Framework

# Check if the script is run from the root of the project
if [ ! -f "composer.json" ]; then
  echo "This script must be run from the root of the project"
  exit 1
fi

# Clone Platform repository from git if not installed
if [ ! -d "Platform" ]; then
  echo "Cloning Platform repository..."
  git clone git@github.com:Ondottr/PHP_SF_platform.git Platform
  echo "Cloning Platform repository... Done"
fi

# Check if composer is installed
if ! [ -x "$(command -v composer)" ]; then
  echo "Composer is not installed"
  exit 1
fi

# Check if the .env file exists
# If not, copy the .env.example file
if [ ! -f ".env" ]; then
  cp .env.example .env
fi

# Check if constants.php file exists
# If not, copy the constants.example.php file
if [ ! -f "config/constants.php" ]; then
  cp config/constants.example.php config/constants.php
fi

# Check if composer dependencies are installed for Platform
# If not, install them
if [ ! -d "Platform/vendor" ]; then
  echo "Installing Platform composer dependencies..."
  cd Platform && composer install --ignore-platform-reqs --no-interaction --no-progress --no-suggest --no-scripts && cd ..
  echo "Installing Platform composer dependencies... Done"
fi

# region Configure .env file

set_db_credentials() {
  echo "Provide the database credentials"

  # Set available databases
  databases=("postgresql" "mysql" "sqlite" "mariadb")

  # Set default port for each database
  declare -A ports
  ports["postgresql"]="5432"
  ports["mysql"]="3306"
  ports["mariadb"]="3306"

  # Set default database versions
  declare -A versions
  versions["postgresql"]="13"
  versions["mysql"]="8"
  versions["mariadb"]="10"

  # Ask for the database credentials
  read -p "Select a database postgresql, mysql, mariadb or sqlite: " database

  # Check if the database is valid
  # shellcheck disable=SC2076
  if [[ ! " ${databases[*]} " =~ " ${database} " ]]; then
    echo "Invalid database, please select one of the following: postgresql, mysql, mariadb or sqlite"
    exit 1
  fi

  default_port=${ports[$database]}

  # If the database is sqlite, ask for the database path
  # If the database is not sqlite, ask for the database user, password, host and port
  if [ "$database" = "sqlite" ]; then
    read -p "Database path: " database_path

    database_url="sqlite://$database_path"
    php -r "file_put_contents('.env', preg_replace('/^#DATABASE_URL=.*/m', 'DATABASE_URL=\"$database_url\"', file_get_contents('.env')));"
  else
    read -p "Database name: " database_name

    read -p "Database user: " database_user
    # Check if the database user is not empty
    if [ -z "$database_user" ]; then
      echo "Database user cannot be empty!"
      exit 1
    fi

    read -p "Database password: " database_password
    # Check if the database password is not empty
    if [ -z "$database_password" ]; then
      echo "Database password cannot be empty!"
      exit 1
    fi

    read -p "Database host (default 127.0.0.1): " database_host
    # Check if the database host is empty and set default value
    if [ -z "$database_host" ]; then
      database_host="127.0.0.1"
    fi

    read -p "Database port (default $default_port):" database_port
    # Check if the database port is empty and set default value
    if [ -z "$database_port" ]; then
      database_port=$default_port
    fi

    # If db is mysql or postgresql aks for the collation
    if [ "$database" = "mysql" ] || [ "$database" = "postgresql" ]; then
      read -p "Database collation (default utf8): " database_collation
      if [ -z "$database_collation" ]; then
        database_collation="utf8"
      fi
    fi

    read -p "Database version (default ${versions[$database]}): " database_version
    if [ -z "$database_version" ]; then
      database_version=${versions[$database]}
    fi

    # Check if the database name is mariadb and then add 'mariadb-' prefix
    if [ "$database" = "mariadb" ]; then
      database_version="mariadb-$database_version"
    fi

    # If the username, password, host or database name contain any character
    # considered special in a URI (such as +, @, $, #, /, :, *, !, %), encode them.
    database_user=$(php -r "echo rawurlencode('$database_user');")
    database_password=$(php -r "echo rawurlencode('$database_password');")
    database_name=$(php -r "echo rawurlencode('$database_name');")
    database_host=$(php -r "echo rawurlencode('$database_host');")

    if [ "$database" = "mariadb" ]; then
      database_url="mysql"
    else
      database_url="$database"
    fi
    database_url="$database_url://$database_user:$database_password@$database_host:$database_port/$database_name"
    # If the database is mysql, mariadb or postgresql, set the database version
    if [ "$database" = "mysql" ] || [ "$database" = "mariadb" ] || [ "$database" = "postgresql" ]; then
      database_url="$database_url?serverVersion=$database_version"
    fi
    # If the database is mysql or postgresql, set the database collation
    if [ "$database" = "mysql" ] || [ "$database" = "postgresql" ]; then
      database_url="$database_url&charset=$database_collation"
    fi
    php -r "file_put_contents('.env', preg_replace('/^#DATABASE_URL=.*/m', 'DATABASE_URL=\"$database_url\"', file_get_contents('.env')));"
  fi

  echo "Database credentials saved"
}

# Check if the database credentials are set
# If not, ask for the database credentials
if grep -q "#DATABASE_URL=" .env; then
  set_db_credentials
fi

set_app_environment() {
  echo "Set application environment prod|dev|test (default dev):"
  read application_environment
  # Check if the application environment is empty and set default value
  if [ -z "$application_environment" ]; then
    application_environment="dev"
  fi
  php -r "file_put_contents('.env', preg_replace('/^#APP_ENV=.*/m', 'APP_ENV=\"$application_environment\"', file_get_contents('.env')));"

  # Ask to enable debug mode
  echo "Enable debug mode? true|false (default false)"
  read debug_mode
  # Check if the debug mode is empty and set default value
  if [ -z "$debug_mode" ]; then
    debug_mode="false"
  fi

  if [ "$debug_mode" = "true" ]; then
    debug_mode="true"
  else
    debug_mode="false"
  fi

  php -r "file_put_contents('.env', preg_replace('/^#APP_DEBUG=.*/m', 'APP_DEBUG=$debug_mode', file_get_contents('.env')));"
}

# Check if the application environment and application debug is set
# If not, ask for the application environment
if grep -q "#APP_ENV=" .env; then
  set_app_environment
fi

set_redis_credentials() {
  echo "Set redis credentials"

  read -p "Redis host (default localhost): " redis_host
  # Check if the redis host is empty and set default value
  if [ -z "$redis_host" ]; then
    redis_host="localhost"
  fi

  read -p "Redis port (default 6379): " redis_port
  # Check if the redis port is empty and set default value
  if [ -z "$redis_port" ]; then
    redis_port="6379"
  fi

  read -p "Redis database (default 0): " redis_db
  # Check if the redis database is empty and set default value
  if [ -z "$redis_db" ]; then
    redis_db="0"
  fi

  php -r "file_put_contents('.env', preg_replace('/^#REDIS_CACHE_URL=.*/m', 'REDIS_CACHE_URL=redis://$redis_host:$redis_port/$redis_db', file_get_contents('.env')));"

  echo "Redis credentials saved"
}

set_memcached_credentials() {
  echo "Set memcached credentials"

  read -p "Memcached host (default localhost): " memcached_host
  # Check if the memcached host is empty and set default value
  if [ -z "$memcached_host" ]; then
    memcached_host="localhost"
  fi

  read -p "Memcached port (default 11211): " memcached_port
  # Check if the memcached port is empty and set default value
  if [ -z "$memcached_port" ]; then
    memcached_port="11211"
  fi

  php -r "file_put_contents('.env', preg_replace('/^#MEMCACHED_SERVER=.*/m', 'MEMCACHED_SERVER=$memcached_host', file_get_contents('.env')));"
  php -r "file_put_contents('.env', preg_replace('/^#MEMCACHED_PORT=.*/m', 'MEMCACHED_PORT=$memcached_port', file_get_contents('.env')));"

  echo "Memcached credentials saved"
}

# Check if the redis credentials are set
# If not, ask for the redis credentials
if grep -q "#REDIS_CACHE_URL=" .env; then
  set_redis_credentials
fi

# Check if the memcached credentials are set
# If not, ask for the memcached credentials
if grep -q "#MEMCACHED_SERVER=" .env; then
  set_memcached_credentials
fi

# Check if server prefix is set
# If not, ask for the server prefix
if grep -q "#SERVER_PREFIX=" .env; then
  echo "Set server prefix: (default server):"
  echo "The server prefix is used to identify the server in the cache"
  echo "If you have multiple servers, you should set a different prefix for each server"
  read server_prefix
  # Check if the server prefix is empty and set default value
  if [ -z "$server_prefix" ]; then
    server_prefix="server"
  fi
  php -r "file_put_contents('.env', preg_replace('/^#SERVER_PREFIX=.*/m', 'SERVER_PREFIX=$server_prefix', file_get_contents('.env')));"
  echo "Server prefix saved"
fi

set_admin_user_credentials() {
  echo "Set admin user credentials"

  read -p "Admin email (default adminemail@example.com): " admin_email
  # Check if the admin user name is empty and set default value
  if [ -z "$admin_email" ]; then
    admin_email="adminemail@example.com"
  fi

  read -p "Admin password (default admin_password): " admin_password
  # Check if the admin password is empty and set default value
  if [ -z "$admin_password" ]; then
    admin_password="admin_password"
  fi

  php -r "file_put_contents('.env', preg_replace('/^#ADMIN_EMAIL=.*/m', 'ADMIN_EMAIL=$admin_email', file_get_contents('.env')));"
  php -r "file_put_contents('.env', preg_replace('/^#ADMIN_PASSWORD=.*/m', 'ADMIN_PASSWORD=$admin_password', file_get_contents('.env')));"
}

# Check if the admin user credentials are set
# If not, ask for the admin user credentials
if grep -q "#ADMIN_EMAIL=" .env; then
  set_admin_user_credentials
fi

# endregion

# region Configure config/constants.php file

# Check if SERVER_IP constant is commented
# If yes, ask for the server ip
if grep -q "^#const SERVER_IP" config/constants.php; then
  echo "Set server ip: (default 127.0.0.1):"

  read server_ip
  # Check if the server ip is empty and set default value
  if [ -z "$server_ip" ]; then
    server_ip="127.0.0.1"
  fi

  php -r "file_put_contents('config/constants.php', preg_replace('/^#const SERVER_IP.*/m', 'const SERVER_IP = \"$server_ip\";', file_get_contents('config/constants.php')));"

  echo "Server ip saved"
fi

set_dev_mode_and_templates_cache() {
  echo "Enable templates cache? true|false (default true)"
  read templates_cache_enabled
  # Check if the templates cache enabled is empty and set default value
  if [ -z "$templates_cache_enabled" ]; then
    templates_cache_enabled="true"
  fi

  if [ "$templates_cache_enabled" = "true" ]; then
    templates_cache_enabled="true"
  else
    templates_cache_enabled="false"
  fi

  php -r "file_put_contents('config/constants.php', preg_replace('/^#const TEMPLATES_CACHE_ENABLED.*/m', 'const TEMPLATES_CACHE_ENABLED = $templates_cache_enabled;', file_get_contents('config/constants.php')));"

  # set DEV_MODE constant
  echo "Enable dev mode? true|false (default false)"
  read dev_mode
  # Check if the dev mode is empty and set default value
  if [ -z "$dev_mode" ]; then
    dev_mode="false"
  fi

  if [ "$dev_mode" = "true" ]; then
    dev_mode="true"
  else
    dev_mode="false"
  fi

  php -r "file_put_contents('config/constants.php', preg_replace('/^#const DEV_MODE.*/m', 'const DEV_MODE = $dev_mode;', file_get_contents('config/constants.php')));"

  echo "Dev mode and templates cache constants saved"
}

# Check if DEV_MODE and TEMPLATES_CACHE_ENABLED constants are commented
# If yes, ask for the dev mode and templates cache
if grep -q "^#const DEV_MODE" config/constants.php; then
  set_dev_mode_and_templates_cache
fi

# Check if APPLICATION_NAME constant is commented
# If yes, ask for the application name
if grep -q "^#const APPLICATION_NAME" config/constants.php; then
  echo "Set application name: (default Platform)"

  read application_name
  # Check if the application name is empty and set default value
  if [ -z "$application_name" ]; then
    application_name="Platform"
  fi

  php -r "file_put_contents('config/constants.php', preg_replace('/^#const APPLICATION_NAME.*/m', 'const APPLICATION_NAME = \"$application_name\";', file_get_contents('config/constants.php')));"

  echo "Application name saved"
fi

# Check if LANGUAGES_LIST constant is commented
# If yes, ask for the languages list
if grep -q "^//define('LANGUAGES_LIST" config/constants.php; then
  echo "You must define or uncomment the LANGUAGES_LIST constant in config/constants.php file manually!"
  echo "All available locales are listed as constants in Platform/src/Classes/Helpers/Locale.php file"
  echo "Application will use the first language from the list as default language"
  echo "Example: define('LANGUAGES_LIST', Locale::getLocaleKey( Locale::en ));"
  echo "Application won't work until you define the LANGUAGES_LIST constant"

  exit 1
fi

# endregion

# region Install dependencies

# Install composer dependencies
fix_symfony_cache_error() {
  # This is a temporary fix for the symfony error
  php -f Platform/app/Command/ClearRedundantClassesCommand.php
}

# Check if vendor directory exists
if [ ! -d "vendor" ]; then

  echo "Installing project composer dependencies, it may take a while..."
  composer install --ignore-platform-reqs --no-scripts
  echo "Almost done, fixing symfony errors and installing composer dependencies again..."
  fix_symfony_cache_error
  composer dump-autoload --ignore-platform-reqs --no-scripts
  echo "Composer project dependencies ... Done"
fi

# Install npm dependencies
# Check if node_modules directory exists
if [ ! -d "node_modules" ]; then
  echo "Installing npm dependencies and building assets, it may take a while..."
  yarn install
  cd public/CKEditor || cd .
  yarn install
  cd ../..
  echo "Npm dependencies installed"

  # Build assets
  echo "Building assets..."
  yarn build
  cd public/CKEditor || cd .
  yarn build
  cd ../..
  echo "Assets built"
fi

# endregion

# region Create database

# Ask if is needed to create the database
echo "Create database? [Y/n]"
read answer
# If the answer is empty or y, create the database
if [ -z "$answer" ] || [ "$answer" = "y" ]; then
  php bin/console doctrine:schema:drop -f
  php bin/console doctrine:schema:create

  echo "Database schema created successfully"

  # Ask if is needed to run fixtures
  echo "Run fixtures? y|n (default y)"
  read answer
  # If the answer is empty or y, run fixtures
  if [ -z "$answer" ] || [ "$answer" = "y" ]; then
    php bin/console doctrine:fixtures:custom-loader -f

    echo "Fixtures loaded successfully"
  fi
fi

# endregion

chmod +x run.sh

echo "Installation finished"
echo "You can now run the application with the following command:"
echo '"./run.sh"'