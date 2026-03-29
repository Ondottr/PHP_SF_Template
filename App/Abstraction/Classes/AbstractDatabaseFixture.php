<?php declare( strict_types=1 );

namespace App\Abstraction\Classes;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;
use Throwable;

abstract class AbstractDatabaseFixture extends Fixture
{

    final public function load( ObjectManager $manager ): void
    {
        assert( $manager instanceof EntityManagerInterface );

        try {
            $queries = $this->loadTable();
            if ( is_array( $queries ) === false )
                $queries = [ $queries ];

            foreach ( $queries as $query )
                $manager
                    ->createNativeQuery( $query, new ResultSetMappingBuilder( $manager ) )
                    ->execute();
        } catch ( Throwable $e ) {
            throw new RuntimeException( 'Error while inserting data to the table: ' . $e->getMessage(), previous: $e );
        }

        try {
            $queries = $this->loadFunctions();
            if ( is_array( $queries ) === false )
                $queries = [ $queries ];

            foreach ( $queries as $query )
                $manager
                    ->createNativeQuery( $query, new ResultSetMappingBuilder( $manager ) )
                    ->execute();
        } catch ( Throwable $e ) {
            throw new RuntimeException( 'Error while creating functions: ' . $e->getMessage(), previous: $e );
        }

        try {
            $queries = $this->loadTriggers();
            if ( is_array( $queries ) === false )
                $queries = [ $queries ];

            foreach ( $queries as $query )
                $manager
                    ->createNativeQuery( $query, new ResultSetMappingBuilder( $manager ) )
                    ->execute();
        } catch ( Throwable $e ) {
            throw new RuntimeException( 'Error while creating triggers: ' . $e->getMessage(), previous: $e );
        }
    }


    /**
     * @return array|string List of queries or one query to be executed to insert all required data to the table
     */
    abstract protected function loadTable(): array|string;

    /**
     * @return array|string List of queries or one query to be executed to create all required functions
     */
    protected function loadFunctions(): array|string { return []; }

    /**
     * @return array|string List of queries or one query to be executed to create all required triggers
     */
    protected function loadTriggers(): array|string { return []; }

}
