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

namespace App\View\Layout;

use App\Kernel;
use App\Entity\User;
use PHP_SF\System\Router;
use PHP_SF\System\Core\Response;
use PHP_SF\System\Database\DoctrineEntityManager;
use App\View\Layout\FooterComponents\CKEditor_activator;
use PHP_SF\System\Classes\Abstracts\AbstractEventsDispatcher;


final class footer extends \PHP_SF\Templates\Layout\footer
{
    /** @noinspection ForgottenDebugOutputInspection */
    public function show(): void
    { ?>

      </div>
      <div class="footer">
        <div class="container">
          <div class="row">
            <div class="col-6">
                <?php if (User::isAdmin())
                    dump(DoctrineEntityManager::getDbRequestsList()) ?>
            </div>
            <div class="col-6"><?php
                $key = sprintf('%s:average_page_load_time', SERVER_NAME);
                rp()->rpush($key, [($currentPageLoadTime = round((microtime(true) - start_time), 5))]);
                if (!(bool)rc()->exists($key))
                    rp()->expire($key, 86400);

                $sum = $currentPageLoadTime;
                foreach (($arr = rc()->lrange($key, 0, -1)) as $value)
                    $sum += $value;

                $averagePageLoadTime = round($sum / (count($arr) + 1), 5);

                ?>
              <p>
                  <?= round($currentPageLoadTime, 3) . ' ' . _t('sec') ?>
                / <?= round($averagePageLoadTime, 3) . ' ' . _t('sec') ?>
              </p>
            </div>
          </div>

            <?php if (User::isAdmin()) : ?>
              <div class="row">
                <div class="col-6">
                    <?php dump(Router::$currentRoute, AbstractEventsDispatcher::getDispatchedListeners()) ?>
                </div>
                <div class="col-6">
                    <?php dump(Response::$activeTemplates) ?>
                </div>
              </div>
            <?php endif ?>

        </div>
      </div>


      </body>
      </html>

        <?php
        if (Kernel::isEditorActivated())
            $this->import(CKEditor_activator::class);

    }

}
