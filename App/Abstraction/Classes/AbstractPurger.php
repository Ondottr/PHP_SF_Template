<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2026, Nations Original Sp. z o.o. <contact@nations-original.com>
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

use App\Abstraction\Interfaces\CustomPurgerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

abstract class AbstractPurger implements ORMPurgerInterface, CustomPurgerInterface
{

    private EntityManagerInterface $em;


    final public function purge(): void
    {
        foreach ( $this->getQueries() as $q )
            $this->em
                ->createNativeQuery( $q, new ResultSetMappingBuilder( $this->em ) )
                ->execute();
    }

    final public function setEntityManager( EntityManagerInterface $em ): void
    {
        $this->em = $em;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * Returns the name of the entity manager this purger belongs to.
     * Must match a key under doctrine.orm.entity_managers in doctrine.yaml.
     */
    abstract public function getEntityManagerName(): string;

    /**
     * Returns an array of queries to execute.<p>
     *
     * @return array<string>
     */
    abstract protected function getQueries(): array;

}
