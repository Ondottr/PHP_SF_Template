<?php declare(strict_types=1);

namespace App\View\Components;

use PHP_SF\System\Classes\Abstracts\AbstractView;

/**
 * @property string $startPageRoute
 * @property string $startPageName
 * @property string $backPageLink
 */
final class pages_header extends AbstractView
{
    public function show(): void
    { ?>

      <h4>
        <a href="<?= $this->startPageRoute ?>"><?= $this->startPageName ?></a>

          <?php if (isset($this->backPageLink)) : ?>
            | <a href="<?= $this->backPageLink ?>"><?= _t('go_back') ?></a>
          <?php endif; ?>
      </h4>

      <hr />
    <?php }
}
