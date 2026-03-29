<?php declare( strict_types=1 );

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
