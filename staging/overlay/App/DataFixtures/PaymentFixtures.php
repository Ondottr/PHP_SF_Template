<?php declare( strict_types=1 );

namespace App\DataFixtures;

use App\DataFixtures\FakerFactory\PaymentFactory;
use App\Entity\Payments\Payment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates sample Payment records covering all MariaDB column types defined on the entity.
 *
 * Silently skips when the given ObjectManager does not manage Payment
 * (allows running --em for any connection without errors).
 */
final class PaymentFixtures extends Fixture implements FixtureGroupInterface
{

    public static function getGroups(): array { return [ 'payments' ]; }

    public function load( ObjectManager $manager ): void
    {
        if ( $manager->getMetadataFactory()->isTransient( Payment::class ) )
            return;

        PaymentFactory::createMany( 10 );
    }

}
