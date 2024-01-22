<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

namespace App\Abstraction\Classes;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ObjectManager;
use RuntimeException;
use Throwable;

abstract class AbstractDatabaseFixture extends Fixture
{

    final public function load( ObjectManager $manager ): void
    {
        try {
            $queries = $this->loadTable();
            if ( is_array( $queries ) === false )
                $queries = [ $queries ];

            foreach ( $queries as $query )
                em()
                    ->createNativeQuery( $query, new ResultSetMappingBuilder( em() ) )
                    ->execute();
        } catch ( Throwable $e ) {
            throw new RuntimeException( 'Error while inserting data to the table: ' . $e->getMessage(), previous: $e );
        }

        try {
            $queries = $this->loadFunctions();
            if ( is_array( $queries ) === false )
                $queries = [ $queries ];

            foreach ( $queries as $query )
                em()
                    ->createNativeQuery( $query, new ResultSetMappingBuilder( em() ) )
                    ->execute();
        } catch ( Throwable $e ) {
            throw new RuntimeException( 'Error while creating functions: ' . $e->getMessage(), previous: $e );
        }

        try {
            $queries = $this->loadTriggers();
            if ( is_array( $queries ) === false )
                $queries = [ $queries ];

            foreach ( $queries as $query )
                em()
                    ->createNativeQuery( $query, new ResultSetMappingBuilder( em() ) )
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
    abstract protected function loadFunctions(): array|string;

    /**
     * @return array|string List of queries or one query to be executed to create all required triggers
     */
    abstract protected function loadTriggers(): array|string;

}