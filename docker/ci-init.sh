#!/bin/sh
# ci-init.sh — Non-interactive bootstrap for CI and staging.
#
# This script creates only the generated artefacts that init.sh produces

set -e

mkdir -p App/Entity/Main App/Entity/Dummy App/Entity/Products App/Entity/Payments \
         App/Repository/Main App/Repository/Products App/Repository/Payments

# ── User entity ───────────────────────────────────────────────────────────────
cat > App/Entity/Main/User.php << 'PHP'
<?php declare( strict_types=1 );

namespace App\Entity\Main;

use App\Repository\Main\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Attributes\Validator\Constraints as Validate;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\System\Traits\ModelProperty\ModelPropertyCreatedAtTrait;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: 'users', schema: 'public' )]
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
PHP

# ── UserRepository ────────────────────────────────────────────────────────────
cat > App/Repository/Main/UserRepository.php << 'PHP'
<?php declare( strict_types=1 );

namespace App\Repository\Main;

use App\Entity\Main\User;
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
        parent::__construct( em( 'main' ), em( 'main' )->getClassMetadata( User::class ) );
    }
}
PHP

# ── Product entity (MySQL) ────────────────────────────────────────────────────
cat > App/Entity/Products/Product.php << 'PHP'
<?php declare( strict_types=1 );

namespace App\Entity\Products;

use App\Repository\Products\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;

#[ORM\Entity( repositoryClass: ProductRepository::class )]
#[ORM\Table( name: 'products' )]
class Product extends AbstractEntity
{
}
PHP

cat > App/Repository/Products/ProductRepository.php << 'PHP'
<?php declare( strict_types=1 );

namespace App\Repository\Products;

use App\Entity\Products\Product;
use PHP_SF\System\Classes\Abstracts\AbstractEntityRepository;

/**
 * @method Product|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Product|null findOneBy( array $criteria, array $orderBy = null )
 * @method Product[]    findAll()
 * @method Product[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class ProductRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct( em( 'products' ), em( 'products' )->getClassMetadata( Product::class ) );
    }
}
PHP

# ── Payment entity (MariaDB) ──────────────────────────────────────────────────
cat > App/Entity/Payments/Payment.php << 'PHP'
<?php declare( strict_types=1 );

namespace App\Entity\Payments;

use App\Repository\Payments\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;

#[ORM\Entity( repositoryClass: PaymentRepository::class )]
#[ORM\Table( name: 'payments' )]
class Payment extends AbstractEntity
{
}
PHP

cat > App/Repository/Payments/PaymentRepository.php << 'PHP'
<?php declare( strict_types=1 );

namespace App\Repository\Payments;

use App\Entity\Payments\Payment;
use PHP_SF\System\Classes\Abstracts\AbstractEntityRepository;

/**
 * @method Payment|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Payment|null findOneBy( array $criteria, array $orderBy = null )
 * @method Payment[]    findAll()
 * @method Payment[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PaymentRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct( em( 'payments' ), em( 'payments' )->getClassMetadata( Payment::class ) );
    }
}
PHP

# ── Wire User entity into entry points ───────────────────────────────────────
php << 'PHP'
<?php
$use_stmt   = 'use App\Entity\Main\User;';
$set_method = '->setApplicationUserClassName( User::class )';

foreach (['bin/console', 'public/index.php', 'tests/bootstrap.php'] as $file) {
    if (!file_exists($file)) {
        continue;
    }
    $content = file_get_contents($file);

    if (strpos($content, $use_stmt) === false) {
        $content = preg_replace(
            '/^(use (?:App\\\\Kernel|Symfony\\\\Component\\\\Dotenv\\\\Dotenv);)/m',
            '$1' . "\n" . $use_stmt,
            $content,
            1
        );
    }

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
PHP

# ── Doctrine config for extra EMs (never committed — gitignored) ──────────────
cat > config/packages/doctrine.staging.yaml << 'YAML'
doctrine:
    dbal:
        connections:
            main:
                driver: pdo_pgsql
                host: '%env(DATABASE_MAIN_HOST)%'
                port: '%env(int:DATABASE_MAIN_PORT)%'
                user: '%env(DATABASE_MAIN_USER)%'
                password: '%env(DATABASE_MAIN_PASSWORD)%'
                dbname: '%env(DATABASE_MAIN_DBNAME)%'
                server_version: '%env(DATABASE_MAIN_VERSION)%'
                charset: utf8
            products:
                driver: pdo_mysql
                host: '%env(DATABASE_PRODUCTS_HOST)%'
                port: '%env(int:DATABASE_PRODUCTS_PORT)%'
                user: '%env(DATABASE_PRODUCTS_USER)%'
                password: '%env(DATABASE_PRODUCTS_PASSWORD)%'
                dbname: '%env(DATABASE_PRODUCTS_DBNAME)%'
                server_version: '%env(DATABASE_PRODUCTS_VERSION)%'
                charset: utf8
                mapping_types:
                    enum: string
            payments:
                driver: pdo_mysql
                host: '%env(DATABASE_PAYMENTS_HOST)%'
                port: '%env(int:DATABASE_PAYMENTS_PORT)%'
                user: '%env(DATABASE_PAYMENTS_USER)%'
                password: '%env(DATABASE_PAYMENTS_PASSWORD)%'
                dbname: '%env(DATABASE_PAYMENTS_DBNAME)%'
                server_version: '%env(DATABASE_PAYMENTS_VERSION)%'
                mapping_types:
                    enum: string
    orm:
        entity_managers:
            main:
                connection: main
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    Main:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/App/Entity/Main'
                        prefix: App\Entity\Main
                        alias: Main
            products:
                connection: products
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    Products:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/App/Entity/Products'
                        prefix: App\Entity\Products
                        alias: Products
            payments:
                connection: payments
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    Payments:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/App/Entity/Payments'
                        prefix: App\Entity\Payments
                        alias: Payments
when@test:
    doctrine:
        dbal:
            connections:
                main:
                    dbname: '%env(default:app.db_main_test_dbname:DATABASE_MAIN_DBNAME_TEST)%'
                products:
                    dbname: '%env(default:app.db_products_test_dbname:DATABASE_PRODUCTS_DBNAME_TEST)%'
                payments:
                    dbname: '%env(default:app.db_payments_test_dbname:DATABASE_PAYMENTS_DBNAME_TEST)%'
parameters:
    app.db_main_test_dbname: '%env(DATABASE_MAIN_DBNAME)%_test'
    app.db_products_test_dbname: '%env(DATABASE_PRODUCTS_DBNAME)%_test'
    app.db_payments_test_dbname: '%env(DATABASE_PAYMENTS_DBNAME)%_test'
YAML

echo "ci-init.sh: entity and repository stubs created."
