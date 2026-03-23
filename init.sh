#!/usr/bin/env bash

# init.sh — Initialize a brand-new PHP_SF project
#
# Run this ONCE when setting up a new project from the template.
# It configures .env, doctrine.yaml, and creates entity/repository directories.
#
# For installing an already-configured project on a new machine, use install.sh instead.

set -e

# Check if the script is run from the root of the project
if [ ! -f "composer.json" ]; then
  echo "This script must be run from the root of the project"
  exit 1
fi

if ! [ -x "$(command -v composer)" ]; then
  echo "Composer is not installed"
  exit 1
fi

# region Detect existing configuration

already_configured=false
declare -a existing_ems=()

if [ -f "vendor/autoload.php" ]; then
  connections_json=$(php -r "
    require_once 'vendor/autoload.php';
    use Symfony\Component\Yaml\Yaml;
    \$yaml = Yaml::parseFile('config/packages/doctrine.yaml');
    \$connections = array_keys(\$yaml['doctrine']['dbal']['connections'] ?? []);
    \$real = array_filter(\$connections, fn(\$c) => \$c !== 'dummy');
    echo json_encode(array_values(\$real));
  " 2>/dev/null || echo '[]')

  if [ "$connections_json" != "[]" ] && [ -n "$connections_json" ]; then
    already_configured=true
    while IFS= read -r em; do
      [ -n "$em" ] && existing_ems+=("$em")
    done < <(php -r "foreach (json_decode('$connections_json', true) as \$em) echo \$em . PHP_EOL;")
  fi
fi

# endregion

# region Helpers

to_pascal_case() {
  php -r "echo str_replace(' ', '', ucwords(str_replace('_', ' ', '$1')));"
}

# Adds a DBAL connection + ORM entity manager to doctrine.yaml using Symfony Yaml.
# $1 — em_name       (e.g. "blog")
# $2 — driver        (e.g. "pdo_pgsql" or "pdo_mysql")
# $3 — server_version (e.g. "16.1" or "mariadb-11.0.0")
# $4 — "true" if mapping_types enum:string should be added (mysql/mariadb)
# $5 — pascal_name   (e.g. "Blog")
# $6 — dbname        (e.g. "my_database")
# $7 — charset       (e.g. "utf8", empty for mariadb)
update_doctrine_yaml() {
  local em_name="$1"
  local driver="$2"
  local server_version="$3"
  local has_mapping_types="$4"
  local pascal_name="$5"
  local dbname="$6"
  local charset="$7"
  local em_upper="${em_name^^}"

  local php_script
  php_script=$(mktemp /tmp/update_doctrine_XXXXXX.php)

  # Use single-quoted heredoc so PHP variables are not expanded by bash;
  # shell variables below are injected via argv.
  cat > "$php_script" << 'PHPEOF'
<?php
require_once 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

[$em_name, $driver, $server_version, $has_mapping_types, $pascal_name, $dbname, $charset, $em_upper]
    = array_slice($argv, 1);

$file    = 'config/packages/doctrine.yaml';
$content = file_get_contents($file);

// Preserve leading header comments (lines starting with # or blank, before 'doctrine:')
preg_match('/^((?:#[^\n]*\n|\n)*)/', $content, $m);
$header = $m[1] ?? '';

$yaml = Yaml::parse($content);

// ── DBAL connection ────────────────────────────────────────────────────────
$conn = [
    'driver'         => $driver,
    'host'           => "%env(DATABASE_{$em_upper}_HOST)%",
    'port'           => "%env(int:DATABASE_{$em_upper}_PORT)%",
    'user'           => "%env(DATABASE_{$em_upper}_USER)%",
    'password'       => "%env(DATABASE_{$em_upper}_PASSWORD)%",
    'dbname'         => "%env(DATABASE_{$em_upper}_DBNAME)%",
    'server_version' => "%env(DATABASE_{$em_upper}_VERSION)%",
];
if ($charset !== '') {
    $conn['charset'] = $charset;
}
if ($has_mapping_types === 'true') {
    $conn['mapping_types'] = ['enum' => 'string'];
}
$yaml['doctrine']['dbal']['connections'][$em_name] = $conn;

// ── ORM entity manager ─────────────────────────────────────────────────────
$em = [
    'connection' => $em_name,
    'mappings'   => [
        $pascal_name => [
            'is_bundle' => false,
            'type'      => 'attribute',
            'dir'       => "%kernel.project_dir%/App/Entity/{$pascal_name}",
            'prefix'    => "App\\Entity\\{$pascal_name}",
            'alias'     => $pascal_name,
        ],
    ],
];
// MySQL/MariaDB: schema in ORM\Table is treated as database prefix by DDL but
// DBAL introspects unqualified names — use UnqualifiedTableQuoteStrategy to prevent
// doctrine:schema:update from generating spurious CREATE + DROP pairs.
if ($driver === 'pdo_mysql') {
    $em['quote_strategy'] = 'App\\Doctrine\\UnqualifiedTableQuoteStrategy';
}
$yaml['doctrine']['orm']['entity_managers'][$em_name] = $em;

// ── Fallback parameter: _test suffix when DBNAME_TEST is not set ───────────
$yaml['parameters']["app.db_{$em_name}_test_dbname"] = "%env(DATABASE_{$em_upper}_DBNAME)%_test";

// ── when@test: use DBNAME_TEST if set, otherwise fall back to DBNAME + _test
if (!isset($yaml['when@test']['doctrine']['dbal']['connections'])
    || $yaml['when@test']['doctrine']['dbal']['connections'] === null) {
    $yaml['when@test']['doctrine']['dbal']['connections'] = [];
}
$yaml['when@test']['doctrine']['dbal']['connections'][$em_name] = [
    'dbname' => "%env(default:app.db_{$em_name}_test_dbname:DATABASE_{$em_upper}_DBNAME_TEST)%",
];

$output = $header . Yaml::dump($yaml, 10, 4, Yaml::DUMP_NULL_AS_TILDE);
file_put_contents($file, $output);
PHPEOF

  php "$php_script" \
    "$em_name" "$driver" "$server_version" "$has_mapping_types" \
    "$pascal_name" "$dbname" "$charset" "$em_upper"
  rm -f "$php_script"
}

# endregion

# region DB connection helpers

declare -a configured_ems=()
declare -A em_schemas=()

declare -A db_default_ports=( [postgresql]=5432 [mysql]=3306 [mariadb]=3306 )
declare -A db_default_versions=( [postgresql]=16 [mysql]=8.0.0 [mariadb]=11.0.0 )

configure_db_connection() {
  local em_name="$1"
  local em_upper="${em_name^^}"
  local env_prefix="DATABASE_${em_upper}"

  echo ""
  echo "Configure '$em_name' connection"

  read -r -p "Database type (postgresql, mysql, mariadb) [default: postgresql]: " database
  if [ -z "$database" ]; then database="postgresql"; fi

  if [[ ! "postgresql mysql mariadb" =~ (^| )"$database"( |$) ]]; then
    echo "Invalid database type. Must be one of: postgresql, mysql, mariadb"
    exit 1
  fi

  local driver has_mapping_types="false" default_port default_version
  default_port=${db_default_ports[$database]:-3306}
  default_version=${db_default_versions[$database]:-}

  case "$database" in
    postgresql) driver="pdo_pgsql" ;;
    mysql|mariadb) driver="pdo_mysql"; has_mapping_types="true" ;;
  esac

  local dbname schema database_user database_password database_host database_port
  local charset="" server_version

  read -r -p "Database name: " dbname
  [ -z "$dbname" ] && { echo "Database name cannot be empty!"; exit 1; }

  # Warn if user entered a reserved MySQL/MariaDB system database name
  case "$database" in
    mysql|mariadb)
      case "$dbname" in
        mysql|information_schema|performance_schema|sys)
          echo "WARNING: '$dbname' is a reserved MySQL/MariaDB system database name."
          echo "         Using it will cause doctrine:schema:update to try to DROP system tables."
          echo "         Use a different name (e.g. '${em_name}_db' or your app name)."
          read -r -p "Continue anyway? [y/N]: " _confirm
          [[ "$_confirm" =~ ^[yY]$ ]] || { echo "Aborted."; exit 1; }
          ;;
      esac
      ;;
  esac

  # Determine schema: MySQL/MariaDB have no schema concept separate from the database,
  # so schema = dbname. PostgreSQL schemas are real namespaces; default is 'public'.
  case "$database" in
    mysql|mariadb)
      schema="$dbname"
      ;;
    postgresql)
      read -r -p "Default schema for new entities (default: public): " schema
      [ -z "$schema" ] && schema="public"
      ;;
  esac

  read -r -p "Database user: " database_user
  [ -z "$database_user" ] && { echo "Database user cannot be empty!"; exit 1; }

  read -r -p "Database password: " database_password
  [ -z "$database_password" ] && { echo "Database password cannot be empty!"; exit 1; }

  read -r -p "Database host (default 127.0.0.1): " database_host
  [ -z "$database_host" ] && database_host="127.0.0.1"

  read -r -p "Database port (default $default_port): " database_port
  [ -z "$database_port" ] && database_port=$default_port

  if [ "$database" = "mysql" ] || [ "$database" = "postgresql" ]; then
    read -r -p "Database charset (default utf8): " charset
    [ -z "$charset" ] && charset="utf8"
  fi

  read -r -p "Database version (default $default_version): " server_version
  [ -z "$server_version" ] && server_version=$default_version

  if [ "$database" = "mariadb" ]; then
    server_version=$(php -r "
      \$v = preg_replace('/^mariadb-/i', '', '$server_version');
      \$parts = explode('.', \$v);
      while (count(\$parts) < 3) \$parts[] = '0';
      echo 'mariadb-' . implode('.', \$parts);
    ")
  elif [ "$database" = "mysql" ] || [ "$database" = "postgresql" ]; then
    server_version=$(php -r "
      \$v = '$server_version';
      \$parts = explode('.', \$v);
      while (count(\$parts) < 3) \$parts[] = '0';
      echo implode('.', \$parts);
    ")
  fi

  # Write individual credential vars to .env
  php -r "
    \$env = file_get_contents('.env');
    \$block = \"DATABASE_${em_upper}_HOST=${database_host}\n\"
            . \"DATABASE_${em_upper}_PORT=${database_port}\n\"
            . \"DATABASE_${em_upper}_USER=${database_user}\n\"
            . \"DATABASE_${em_upper}_PASSWORD=${database_password}\n\"
            . \"DATABASE_${em_upper}_DBNAME=${dbname}\n\"
            . \"DATABASE_${em_upper}_VERSION=${server_version}\n\"
            . '###< doctrine/doctrine-bundle ###';
    \$env = str_replace('###< doctrine/doctrine-bundle ###', \$block, \$env);
    file_put_contents('.env', \$env);
  "

  # Write commented placeholder vars to .env.example (HOST/USER/PASSWORD empty, others have defaults)
  php -r "
    \$ex = file_get_contents('.env.example');
    \$block = \"#DATABASE_${em_upper}_HOST=\n\"
            . \"#DATABASE_${em_upper}_PORT=${database_port}\n\"
            . \"#DATABASE_${em_upper}_USER=\n\"
            . \"#DATABASE_${em_upper}_PASSWORD=\n\"
            . \"#DATABASE_${em_upper}_DBNAME=${dbname}\n\"
            . \"#DATABASE_${em_upper}_VERSION=${server_version}\n\"
            . \"# Optional: override test DB name (default: DATABASE_${em_upper}_DBNAME + _test)\n\"
            . \"#DATABASE_${em_upper}_DBNAME_TEST=\n\"
            . '###< doctrine/doctrine-bundle ###';
    \$ex = str_replace('###< doctrine/doctrine-bundle ###', \$block, \$ex);
    file_put_contents('.env.example', \$ex);
  "

  # Update doctrine.yaml and create directories
  local pascal_name
  pascal_name=$(to_pascal_case "$em_name")
  update_doctrine_yaml "$em_name" "$driver" "$server_version" "$has_mapping_types" "$pascal_name" "$dbname" "$charset"

  mkdir -p "App/Entity/$pascal_name"
  mkdir -p "App/Repository/$pascal_name"
  mkdir -p "App/Maker"
  echo "Created App/Entity/$pascal_name/ and App/Repository/$pascal_name/"

  generate_maker "$em_name" "$pascal_name" "$schema"

  configured_ems+=("$em_name")
  em_schemas["$em_name"]="$schema"
  echo "'$em_name' connection configured"
}

generate_maker() {
  local em_name="$1"
  local pascal_name="$2"
  local schema="$3"
  local maker_file="App/Maker/Make${pascal_name}Entity.php"

  cat > "$maker_file" << 'PHPEOF'
<?php declare( strict_types=1 );

namespace App\Maker;

use PHP_SF\System\Classes\Abstracts\AbstractEntityMaker;

final class Make__PASCAL__Entity extends AbstractEntityMaker
{

    protected string $entityNamespace     = 'App\Entity\__PASCAL__';
    protected string $repositoryNamespace = 'App\Repository\__PASCAL__';
    protected string $entityDir           = __DIR__ . '/../Entity/__PASCAL__';
    protected string $repositoryDir       = __DIR__ . '/../Repository/__PASCAL__';
    protected string $schema              = '__SCHEMA__';

    public static function getCommandName(): string
    {
        return '__EM__:make:entity';
    }

}
PHPEOF

  sed -i "s/__PASCAL__/${pascal_name}/g; s/__EM__/${em_name}/g; s/__SCHEMA__/${schema}/g" "$maker_file"
  echo "Created $maker_file (command: ${em_name}:make:entity)"
}

create_user_entity() {
  local em_name="$1"
  local pascal_name="$2"
  local schema="$3"
  # ── User entity ──────────────────────────────────────────────────────
  cat > "App/Entity/${pascal_name}/User.php" << 'PHPEOF'
<?php declare( strict_types=1 );

namespace App\Entity\__PASCAL__;

use App\Repository\__PASCAL__\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\Framework\Http\Middleware\auth;
use PHP_SF\System\Attributes\Validator\Constraints as Validate;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: 'users', schema: '__SCHEMA__' )]
#[ORM\Cache( usage: 'READ_WRITE' )]
class User extends AbstractEntity implements UserInterface
{
    use ModelPropertyCreatedAtTrait;


    #[Validate\Email]
    #[Validate\Length( min: 6, max: 50 )]
    #[TranslatablePropertyName( 'Email' )]
    #[ORM\Column( type: 'string', unique: true )]
    protected ?string $email = null;

    #[TranslatablePropertyName( 'Password' )]
    #[ORM\Column( type: 'string' )]
    protected ?string $password = null;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }


    public function setEmail( ?string $email ): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword( ?string $password ): self
    {
        if ( $password !== null )
            $this->password = password_hash( $password, PASSWORD_BCRYPT );

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

}
PHPEOF

  sed -i "s/__PASCAL__/${pascal_name}/g; s/__EM__/${em_name}/g; s/__SCHEMA__/${schema}/g" "App/Entity/${pascal_name}/User.php"

  # ── UserRepository ───────────────────────────────────────────────────
  cat > "App/Repository/${pascal_name}/UserRepository.php" << 'PHPEOF'
<?php declare( strict_types=1 );

namespace App\Repository\__PASCAL__;

use App\Entity\__PASCAL__\User;
use PHP_SF\System\Classes\Abstracts\AbstractEntityRepository;

/**
 * @method User|null find( $id, $lockMode = null, $lockVersion = null )
 * @method User|null findOneBy( array $criteria, array $orderBy = null )
 * @method User[]    findAll()
 * @method User[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class UserRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct( em( '__EM__' ), em( '__EM__' )->getClassMetadata( User::class ) );
    }
}
PHPEOF

  sed -i "s/__PASCAL__/${pascal_name}/g; s/__EM__/${em_name}/g" "App/Repository/${pascal_name}/UserRepository.php"

  echo "Created App/Entity/${pascal_name}/User.php"
  echo "Created App/Repository/${pascal_name}/UserRepository.php"

  # ── Update the 3 framework entry-points ──────────────────────────────
  local use_stmt_b64
  use_stmt_b64=$(printf 'use App\\Entity\\%s\\User;' "$pascal_name" | base64 -w 0)

  local php_script
  php_script=$(mktemp /tmp/patch_entrypoints_XXXXXX.php)

  cat > "$php_script" << 'PHPEOF'
<?php
$use_stmt   = base64_decode($argv[1]);
$set_method = '->setApplicationUserClassName( User::class )';

foreach (['bin/console', 'public/index.php', 'tests/bootstrap.php'] as $file) {
    if (!file_exists($file)) {
        continue;
    }
    $content = file_get_contents($file);

    // Insert use statement after 'use App\Kernel;' or 'use Symfony\Component\Dotenv\Dotenv;'
    if (strpos($content, $use_stmt) === false) {
        $content = preg_replace(
            '/^(use (?:App\\\\Kernel|Symfony\\\\Component\\\\Dotenv\\\\Dotenv);)/m',
            '$1' . "\n" . $use_stmt,
            $content,
            1
        );
    }

    // Insert ->setApplicationUserClassName before the ; that ends the $kernel chain.
    // Find the last standalone ';' line (no other content) and insert before it,
    // using the same indent as the preceding method call line.
    if (strpos($content, 'setApplicationUserClassName') === false) {
        $lines = explode("\n", $content);
        for ($i = count($lines) - 1; $i >= 1; $i--) {
            if (trim($lines[$i]) === ';') {
                preg_match('/^([ \t]*)/', $lines[$i - 1], $m);
                $indent = $m[1];
                array_splice($lines, $i, 0, $indent . $set_method);
                break;
            }
        }
        $content = implode("\n", $lines);
    }

    file_put_contents($file, $content);
}
PHPEOF

  php "$php_script" "$use_stmt_b64"
  rm -f "$php_script"

  echo "Updated bin/console, public/index.php, tests/bootstrap.php"
  echo ""
  echo "User entity created in App/Entity/${pascal_name}/User.php"
  echo "Use bin/console ${em_name}:make:entity to generate more entities in this connection."
}

# endregion

# region Main flow

if $already_configured; then

  # ── Already-initialized project ────────────────────────────────────────────
  echo ""
  echo "This project already has the following database connections configured:"
  printf '  - %s\n' "${existing_ems[@]}"
  echo ""
  echo "init.sh is designed for initial project setup only."
  echo "Running the full initialization on an already-configured project could overwrite"
  echo "your existing .env, doctrine.yaml, and other configuration files."
  echo ""
  read -r -p "Do you want to add another database connection? [y/N]: " add_another
  if [[ ! "$add_another" =~ ^[yY]$ ]]; then
    echo ""
    echo "Aborted. Use install.sh to set up an already-configured project on a new machine."
    exit 0
  fi

  db_prompt="Add a database connection? [Y/n]: "
  while true; do
    echo ""
    read -r -p "$db_prompt" add_conn
    if [ -z "$add_conn" ] || [ "$add_conn" = "y" ] || [ "$add_conn" = "Y" ]; then
      read -r -p "Entity manager name (e.g. main, blog, payments, catalog): " em_name
      [ -z "$em_name" ] && { echo "Entity manager name cannot be empty"; continue; }
      configure_db_connection "$em_name"
      db_prompt="Add another database connection? [Y/n]: "
    else
      break
    fi
  done

  # Check whether the User entity is present and properly wired
  echo ""
  user_entity_file=""
  for f in App/Entity/*/User.php; do
    if [ -f "$f" ]; then
      user_entity_file="$f"
      break
    fi
  done

  if [ -n "$user_entity_file" ]; then
    echo "User entity found at $user_entity_file."
    if grep -q "setApplicationUserClassName" bin/console 2>/dev/null; then
      echo "Entry points are configured correctly with the User entity."
    else
      echo "WARNING: Entry points (bin/console, public/index.php, tests/bootstrap.php) do not"
      echo "         appear to have setApplicationUserClassName() configured."
      echo "         Please add it manually, or re-run init.sh from scratch on a clean project."
    fi
  else
    echo "No User entity found."
    echo "The User entity is required for the PHP_SF framework to function (authentication,"
    echo "session handling, and access control all depend on it)."
    echo ""
    all_ems=("${existing_ems[@]}" "${configured_ems[@]}")
    user_em=""
    if [ ${#all_ems[@]} -eq 1 ]; then
      user_em="${all_ems[0]}"
      echo "Using entity manager '$user_em' for the User entity."
    elif [ ${#all_ems[@]} -gt 1 ]; then
      echo "Which entity manager should contain the User entity? (${all_ems[*]})"
      read -r -p "Entity manager name: " user_em
      [ -z "$user_em" ] && user_em="${all_ems[0]}"
    fi
    if [ -n "$user_em" ]; then
      user_pascal=$(to_pascal_case "$user_em")
      user_schema="${em_schemas[$user_em]:-public}"
      create_user_entity "$user_em" "$user_pascal" "$user_schema"
    fi
  fi

else

  # ── Full initialization ────────────────────────────────────────────────────

  if [ -f ".env" ]; then
    echo "ERROR: .env already exists but doctrine.yaml has no configured connections."
    echo "This is an inconsistent state. Please check your configuration manually."
    exit 1
  fi

  # Copy example files
  cp .env.example .env
  if [ ! -f "config/constants.php" ]; then
    cp config/constants.example.php config/constants.php
  fi

  # region Install dependencies (must be first — update_doctrine_yaml requires vendor/autoload.php)

  if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --ignore-platform-reqs --no-scripts
  fi

  # endregion

  # region DB connections

  db_prompt="Add a database connection? [Y/n]: "
  while true; do
    echo ""
    read -r -p "$db_prompt" add_conn
    if [ -z "$add_conn" ] || [ "$add_conn" = "y" ] || [ "$add_conn" = "Y" ]; then
      read -r -p "Entity manager name (e.g. main, blog, payments, catalog): " em_name
      [ -z "$em_name" ] && { echo "Entity manager name cannot be empty"; continue; }
      configure_db_connection "$em_name"
      db_prompt="Add another database connection? [Y/n]: "
    else
      break
    fi
  done

  # endregion

  # region User entity creation

  user_em=""
  if [ ${#configured_ems[@]} -eq 1 ]; then
    user_em="${configured_ems[0]}"
  elif [ ${#configured_ems[@]} -gt 1 ]; then
    echo ""
    echo "The User entity is required by the PHP_SF framework — authentication, session"
    echo "handling, and access control all depend on it. You must assign it to one EM."
    echo "Which entity manager will contain the User entity? (${configured_ems[*]})"
    read -r -p "Entity manager name: " user_em
    if [ -z "$user_em" ]; then user_em="${configured_ems[0]}"; fi
  fi

  user_pascal=$(to_pascal_case "$user_em")

  create_user_entity "$user_em" "$user_pascal" "${em_schemas[$user_em]}"

  # endregion

  # region Other env configuration

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
  read -r -p "Redis host (default localhost): " redis_host;   [ -z "$redis_host" ]   && redis_host="localhost"
  read -r -p "Redis port (default 6379): " redis_port;        [ -z "$redis_port" ]   && redis_port="6379"
  read -r -p "Redis database (default 0): " redis_db;         [ -z "$redis_db" ]     && redis_db="0"
  php -r "file_put_contents('.env', preg_replace('/^#REDIS_CACHE_URL=.*/m', 'REDIS_CACHE_URL=redis://$redis_host:$redis_port/$redis_db', file_get_contents('.env')));"

  echo ""
  echo "Set Memcached credentials"
  read -r -p "Memcached host (default localhost): " memcached_host; [ -z "$memcached_host" ] && memcached_host="localhost"
  read -r -p "Memcached port (default 11211): " memcached_port;     [ -z "$memcached_port" ] && memcached_port="11211"
  php -r "file_put_contents('.env', preg_replace('/^#MEMCACHED_SERVER=.*/m', 'MEMCACHED_SERVER=$memcached_host', file_get_contents('.env')));"
  php -r "file_put_contents('.env', preg_replace('/^#MEMCACHED_PORT=.*/m', 'MEMCACHED_PORT=$memcached_port', file_get_contents('.env')));"

  echo ""
  echo "Set server prefix (default server):"
  echo "The server prefix is used to identify the server in the cache"
  read -r server_prefix; [ -z "$server_prefix" ] && server_prefix="server"
  php -r "file_put_contents('.env', preg_replace('/^#SERVER_PREFIX=.*/m', 'SERVER_PREFIX=$server_prefix', file_get_contents('.env')));"

  echo ""
  echo "Set admin user credentials"
  read -r -p "Admin email (default adminemail@example.com): " admin_email; [ -z "$admin_email" ] && admin_email="adminemail@example.com"
  read -r -p "Admin password (default admin_password): " admin_password;   [ -z "$admin_password" ] && admin_password="admin_password"
  php -r "file_put_contents('.env', preg_replace('/^#ADMIN_EMAIL=.*/m', 'ADMIN_EMAIL=$admin_email', file_get_contents('.env')));"
  php -r "file_put_contents('.env', preg_replace('/^#ADMIN_PASSWORD=.*/m', 'ADMIN_PASSWORD=$admin_password', file_get_contents('.env')));"

  # endregion

  # region Configure config/constants.php

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
    echo ""
    echo "You must define or uncomment the LANGUAGES_LIST constant in config/constants.php manually!"
    echo "See Platform/src/Classes/Helpers/Locale.php for available locales."
    echo "Example: define('LANGUAGES_LIST', Locale::getLocaleKey( Locale::en ));"
    exit 1
  fi

  # endregion

  # region Install npm dependencies and build assets

  if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    yarn install
    cd public/CKEditor || cd .
    yarn install
    cd ../..

    if [ -d "public/CKEditor" ]; then
      echo "Building assets..."
      yarn build
      cd public/CKEditor || cd .
      yarn build
      cd ../..
    fi

    php bin/console assets:install
  fi

  # endregion

  # region Build Codeception actors

  echo "Building Codeception actor classes..."
  vendor/bin/codecept build --quiet

  # endregion

  # region Create schemas and load fixtures

  if [ ${#configured_ems[@]} -gt 0 ]; then
    echo ""
    echo "Configured entity managers: ${configured_ems[*]}"
  fi

  # endregion

  chmod +x run.sh

  echo ""
  echo "Initialization complete. You can now run the application with: ./run.sh"
  echo ""

fi

# endregion

# region Keep or delete init.sh

echo ""
read -r -p "Do you want to keep this init.sh script? [Y/n]: " keep_script
if [ "$keep_script" = "n" ] || [ "$keep_script" = "N" ]; then
  rm -- "$0"
  echo "init.sh has been deleted."
else
  echo "init.sh kept."
fi

# endregion