<?php declare(strict_types=1);

namespace App\View\Components\CKEditor;

use PHP_SF\System\Classes\Abstracts\AbstractView;

/**
 * @property string formAction
 * @property string submitButtonText
 * @property string defaultText
 */
class editor_component extends AbstractView
{
    public function show(): void
    { ?>

      <form id="editor" action="<?= $this->formAction ?>" method="post">

        <input id="editor_data" type="hidden" name="editor_data">
        <div class="editor"><?= $this->defaultText ?? '' ?></div>

        <input type="submit" value="<?= $this->submitButtonText ?>" />

      </form>

    <?php }
}
