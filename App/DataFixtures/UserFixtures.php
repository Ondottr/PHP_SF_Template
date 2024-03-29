<?php declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
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

use App\Abstraction\Classes\AbstractDatabaseFixture;
use App\Enums\UserGroupEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class UserFixtures extends AbstractDatabaseFixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [
            UserGroupFixtures::class,
        ];
    }

    protected function loadTable(): array|string
    {
        return [
            sprintf(
                "INSERT INTO users (id, email, password, user_group_id) VALUES (1, '%s', '%s', %s);",
                env('ADMIN_EMAIL'),
                password_hash(env('ADMIN_PASSWORD'), PASSWORD_DEFAULT),
                UserGroupEnum::ADMINISTRATOR->getId()
            ),
        ];
    }

    protected function loadFunctions(): array|string
    {
        return file_get_contents(__DIR__ . '/../../Doctrine/fixtures/user_prevent_admin_deletion_function.sql');
    }

    protected function loadTriggers(): array|string
    {
        return file_get_contents(__DIR__ . '/../../Doctrine/fixtures/user_prevent_admin_deletion_trigger.sql');
    }

}