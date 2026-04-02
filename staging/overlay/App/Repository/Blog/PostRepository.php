<?php declare( strict_types=1 );

namespace App\Repository\Blog;

use App\Entity\Blog\Post;
use PHP_SF\System\Classes\Abstracts\AbstractEntityRepository;

/**
 * @method Post|null find( $id, $lockMode = null, $lockVersion = null )
 * @method Post|null findOneBy( array $criteria, array $orderBy = null )
 * @method Post[]    findAll()
 * @method Post[]    findBy( array $criteria, array $orderBy = null, $limit = null, $offset = null )
 */
class PostRepository extends AbstractEntityRepository
{
    public function __construct()
    {
        parent::__construct( em( 'blog' ), em( 'blog' )->getClassMetadata( Post::class ) );
    }
}
