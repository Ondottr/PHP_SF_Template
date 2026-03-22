<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2026, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

namespace App\DataFixtures\Purgers;

use App\Abstraction\Classes\AbstractPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHP_SF\System\Kernel;
use RuntimeException;

/**
 * Purges the User table before fixtures are loaded.
 *
 * Resolves the entity manager and table name at construction time from Doctrine metadata,
 * so it works with any User entity class and any connection name.
 */
final class UserPurger extends AbstractPurger
{

    private string $emName;
    private string $quotedTable;


    public function __construct( ManagerRegistry $registry )
    {
        $userClass = Kernel::getApplicationUserClassName();

        foreach ( array_keys( $registry->getManagerNames() ) as $name ) {
            if ( $name === 'dummy' )
                continue;

            /** @var EntityManagerInterface $manager */
            $manager = $registry->getManager( $name );

            if ( $manager->getMetadataFactory()->isTransient( $userClass ) )
                continue;

            $metadata = $manager->getClassMetadata( $userClass );
            $table    = $metadata->getTableName();
            $schema   = $metadata->table['schema'] ?? null;

            $this->emName      = $name;
            $this->quotedTable = $schema
                ? $schema . '.' . $table
                : $table;

            return;
        }

        throw new RuntimeException(
            sprintf( 'No entity manager found for User class "%s".', $userClass )
        );
    }


    public function getEntityManagerName(): string
    {
        return $this->emName;
    }

    protected function getQueries(): array
    {
        return [ 'DELETE FROM ' . $this->quotedTable ];
    }

}
