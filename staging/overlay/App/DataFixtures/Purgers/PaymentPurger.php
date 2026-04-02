<?php declare( strict_types=1 );

namespace App\DataFixtures\Purgers;

use App\Abstraction\Classes\AbstractPurger;
use App\Entity\Payments\Payment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Purges the Payment table before fixtures are loaded.
 */
final class PaymentPurger extends AbstractPurger
{

    private string $quotedTable;


    public function __construct( ManagerRegistry $registry )
    {
        /** @var EntityManagerInterface $em */
        $em = $registry->getManager( 'payments' );

        $metadata = $em->getClassMetadata( Payment::class );
        $table    = $metadata->getTableName();
        $schema   = $metadata->table['schema'] ?? null;

        $this->quotedTable = $schema
            ? $schema . '.' . $table
            : $table;
    }


    public function getEntityManagerName(): string { return 'payments'; }

    protected function getQueries(): array
    {
        return [ 'TRUNCATE TABLE ' . $this->quotedTable ];
    }

}
