<?php declare( strict_types=1 );

namespace App\Repository\Payments;

use App\Entity\Payments\Payment;
use PHP_SF\System\Classes\Abstracts\AbstractEntityRepository;

/**
 * @method Payment|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Payment|null findOneBy( array $criteria, array $orderBy = null )
 * @method Payment[]    findAll()
 * @method Payment[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PaymentRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct( em( 'payments' ), em( 'payments' )->getClassMetadata( Payment::class ) );
    }
}
