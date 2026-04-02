<?php declare( strict_types=1 );

namespace App\DataFixtures;

use App\DataFixtures\FakerFactory\PostFactory;
use App\Entity\Blog\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates sample Post records covering all MySQL column types defined on the entity.
 *
 * Silently skips when the given ObjectManager does not manage Post
 * (allows running --em for any connection without errors).
 */
final class PostFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array { return [ 'blog' ]; }

    public function load( ObjectManager $manager ): void
    {
        if ( $manager->getMetadataFactory()->isTransient( Post::class ) )
            return;

        PostFactory::createMany( 10 );
    }

}
