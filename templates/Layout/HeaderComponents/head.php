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

namespace App\View\Layout\HeaderComponents;

use PHP_SF\System\Router;

final class head extends \PHP_SF\Templates\Layout\Header\head
{

    public function show(): void
    { ?>
      <!DOCTYPE html>
    <html lang="<?= DEFAULT_LOCALE ?>">
      <head>
        <script type="text/javascript" src="<?= asset('js/app.js') ?>"></script>
        <script type="text/javascript" src="<?= asset('themes/js/jquery-3.6.0.min.js') ?>"></script>
        <script type="text/javascript" src="<?= asset('themes/js/bootstrap.min.js') ?>"></script>

        <title><?= APPLICATION_NAME ?></title>
        <link rel="shortcut icon" href="<?= asset('images/favicon.gif') ?>">
        <link href="<?= asset('themes/app.css') ?>" rel="stylesheet" type="text/css">

        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0">

      </head>

    <body class="container">
    <span style="position: absolute; right: 0; top: 0; color: #fff; font-weight: bolder; padding: 7px;">
        v. <?= env('DEVELOPMENT_STAGE') ?>
    </span>

    <script>
        app.router.setCurrentRouteName('<?= Router::$currentRoute->name ?>');
    </script>
    <?php }

}
