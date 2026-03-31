<?php declare( strict_types=1 );

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
