<?php declare( strict_types=1 );

namespace App\Abstraction\Classes;

use App\Abstraction\Interfaces\CustomPurgerInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurgerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

abstract class AbstractPurger implements ORMPurgerInterface, CustomPurgerInterface
{

    final public function purge(): void
    {
        foreach ( $this->getQueries() as $q )
            em()
                ->createNativeQuery( $q, new ResultSetMappingBuilder( em() ) )
                ->execute();
    }

    final public function setEntityManager( EntityManagerInterface $em ): void {}

    /**
     * Returns an array of queries to execute.<p>
     *
     * @return array<string>
     */
    abstract protected function getQueries(): array;

}