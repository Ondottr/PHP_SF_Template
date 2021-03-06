<?php declare(strict_types=1);

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

use PHP_SF\System\Classes\Helpers\Locale;
use PHP_SF\System\Classes\Helpers\TimeZone;

const DEV_MODE = true;
const TEMPLATES_CACHE_ENABLED = false;
define('DEFAULT_LOCALE', Locale::getLocaleKey(Locale::en));
const DEFAULT_TIMEZONE = TimeZone::PL;

const SERVER_IP = '127.0.0.1';
const SERVER_NAME = 'php-sf-template';
const APPLICATION_NAME = 'PHP SF Template';

const AVAILABLE_HOSTS = [SERVER_IP, '127.0.0.1'];

const ENTITY_DIRECTORY = __DIR__ . '/../App/Entity';
const DOCTRINE_QUERY_BUILDER_CACHE_TIME = 600;

define('LANGUAGES_LIST', [
    Locale::getLocaleKey(Locale::en),
    Locale::getLocaleKey(Locale::de),
]);
