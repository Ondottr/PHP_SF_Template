<?php declare( strict_types=1 );

namespace App\Repository\Main;

use App\Entity\Main\User;
use PHP_SF\System\Classes\Abstracts\AbstractEntityRepository;

/**
 * @extends AbstractEntityRepository<User>
 * @method User|null find( $id, $lockMode = null, $lockVersion = null )
 * @method User|null findOneBy( array<string, mixed> $criteria, array<string, mixed>|null $orderBy = null )
 * @method User[]    findAll()
 * @method User[]    findBy( array<string, mixed> $criteria, array<string, mixed>|null $orderBy = null, $limit = null, $offset = null )
 */
class UserRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct( em( 'main' ), em( 'main' )->getClassMetadata( User::class ) );
    }
}
