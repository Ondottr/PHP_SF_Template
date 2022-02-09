<?php /** @noinspection PhpClassHasTooManyDeclaredMembersInspection @noinspection PhpLackOfCohesionInspection */
declare( strict_types=1 );

/*
 * Copyright © 2018-2022, Nations Original Sp. z o.o. <contact@nations-original.com>
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

namespace App\Helpers\Controllers;

use App\Entity\User;
use App\Repository\UserRepository;

trait EntityRepositoriesTrait
{
    private UserRepository $userRepository;
    private UserRepository $userCachedRepository;


    public function userRepository( bool $cacheEnabled = true ): UserRepository
    {
        if ( $cacheEnabled )
            return $this->getUserCachedRepository();

        return $this->getUserRepository();
    }

    private function getUserCachedRepository(): UserRepository
    {
        if ( !isset( $this->userCachedRepository ) )
            $this->setUserCachedRepository();

        return $this->userCachedRepository;
    }

    private function setUserCachedRepository(): void
    {
        $this->userCachedRepository = em()->getRepository( User::class );
    }

    private function getUserRepository(): UserRepository
    {
        if ( !isset( $this->userRepository ) )
            $this->setUserRepository();

        return $this->userRepository;
    }

    private function setUserRepository(): void
    {
        $this->userRepository = em( false )->getRepository( User::class );
    }


}
