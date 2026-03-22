<?php declare( strict_types=1 );

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;

/**
 * QuoteStrategy for MySQL/MariaDB entity managers.
 *
 * MySQL/MariaDB equate "schema" with "database" — ORM\Table's schema: attribute
 * causes DDL to use qualified names (schema.table), but DBAL introspects the
 * current database and returns unqualified names (table). This permanent mismatch
 * makes doctrine:schema:update generate spurious CREATE + DROP pairs.
 *
 * This strategy omits the schema qualifier from generated DDL, relying on the
 * DBAL connection's dbname setting to target the correct database. The schema:
 * attribute is still required by EntityAttributesListener for validation purposes.
 */
final class UnqualifiedTableQuoteStrategy extends DefaultQuoteStrategy
{

    public function getTableName( ClassMetadata $class, AbstractPlatform $platform ): string
    {
        $tableName = $class->table['name'];

        return isset( $class->table['quoted'] )
            ? $platform->quoteSingleIdentifier( $tableName )
            : $tableName;
    }

}
