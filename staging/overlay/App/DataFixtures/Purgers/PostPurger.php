<?php declare( strict_types=1 );

namespace App\DataFixtures\Purgers;

use App\Abstraction\Classes\AbstractPurger;
use App\Entity\Blog\Post;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Purges the Post table before fixtures are loaded.
 */
final class PostPurger extends AbstractPurger
{

    private string $quotedTable;


    public function __construct( ManagerRegistry $registry )
    {
        /** @var EntityManagerInterface $em */
        $em = $registry->getManager( 'blog' );

        $metadata = $em->getClassMetadata( Post::class );
        $table    = $metadata->getTableName();
        $schema   = $metadata->table['schema'] ?? null;

        $this->quotedTable = $schema
            ? $schema . '.' . $table
            : $table;
    }


    public function getEntityManagerName(): string { return 'blog'; }

    protected function getQueries(): array
    {
        return [ 'TRUNCATE TABLE ' . $this->quotedTable ];
    }

}
