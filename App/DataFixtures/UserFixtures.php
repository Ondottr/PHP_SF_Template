<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2023, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use PHP_SF\System\Core\DateTime;


final class UserFixtures extends Fixture implements DependentFixtureInterface
{

    public function load( ObjectManager $manager ): void
    {
        $user = new User;

        $user
            ->setEmail( env( 'ADMIN_EMAIL' ) )
            ->setPassword( env( 'ADMIN_PASSWORD' ) )
            ->setCreatedAt( new DateTime )
            ->setUserGroup( UserGroup::find( UserGroup::ADMINISTRATOR ) );

        em()->persist( $user );
        em()->flushUsingTransaction( $user );
    }

    public function getDependencies(): array
    {
        return [
            UserGroupFixtures::class,
        ];
    }

}
