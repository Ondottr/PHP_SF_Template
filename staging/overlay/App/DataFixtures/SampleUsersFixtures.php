<?php declare( strict_types=1 );

namespace App\DataFixtures;

use App\DataFixtures\FakerFactory\UserFactory;
use App\Entity\Main\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates sample User records covering all PostgreSQL column types defined on the entity.
 *
 * Complements UserFixtures (which creates the admin account) by adding test users
 * with randomised type-coverage data via Foundry/Faker.
 *
 * Silently skips when the given ObjectManager does not manage User
 * (allows running --em for any connection without errors).
 */
final class SampleUsersFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array { return [ 'main' ]; }

    public function load( ObjectManager $manager ): void
    {
        if ( $manager->getMetadataFactory()->isTransient( User::class ) )
            return;

        UserFactory::createMany( 5 );
    }

}
