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

final class UserGroupFixtures extends AbstractDatabaseFixture
{

    protected function loadTable(): array|string
    {
        return <<<SQL
INSERT INTO user_groups (id, name)
VALUES
    (-1, 'banned'),
    (1, 'administrator'),
    (3, 'moderator'),
    (6, 'user');
SQL;
    }

    protected function loadFunctions(): array|string
    {
        return file_get_contents( __DIR__ . '/../../Doctrine/fixtures/user_groups_prevent_any_changes_function.sql' );
    }

    protected function loadTriggers(): array|string
    {
        return file_get_contents( __DIR__ . '/../../Doctrine/fixtures/user_groups_prevent_any_changes_trigger.sql' );
    }

}