<?php declare( strict_types=1 );

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use PHP_SF\System\Interface\UserInterface;
use PHP_SF\System\Kernel;
use RuntimeException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Creates the initial admin user using credentials from ADMIN_EMAIL / ADMIN_PASSWORD env vars.
 *
 * Works with any User entity class registered via Kernel::setApplicationUserClassName(),
 * as long as it implements UserInterface.  Silently skips when the given ObjectManager
 * does not manage the User class (allows running --em for any connection without errors).
 */
final class UserFixtures extends Fixture
{

    public function load( ObjectManager $manager ): void
    {
        try {
            $userClass = Kernel::getApplicationUserClassName();
        } catch ( InvalidConfigurationException $e ) {
            throw new RuntimeException(
                'UserFixtures requires setApplicationUserClassName() to be called in bin/console. Run init.sh first.',
                0,
                $e
            );
        }

        if ( $manager->getMetadataFactory()->isTransient( $userClass ) )
            return;

        /** @var UserInterface $user */
        $user = new $userClass();
        $user->setEmail( env( 'ADMIN_EMAIL', 'admin@example.com' ) );
        $user->setPassword( env( 'ADMIN_PASSWORD', 'admin_password' ) );

        $manager->persist( $user );
        $manager->flush();
    }

}
