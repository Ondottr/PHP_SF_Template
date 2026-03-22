<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2026, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

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
