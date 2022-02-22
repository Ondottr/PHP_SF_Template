<?php declare( strict_types=1 );

namespace App\View\Components\CKEditor;

use App\Kernel;
use PHP_SF\System\Classes\Abstracts\AbstractView;

/**
 * @property string defaultText
 */
class editor_component extends AbstractView
{
    public function show(): void
    {
        Kernel::setEditorStatus( true ) ?>

        <input id="editor_data" type="hidden" name="editor_data">
        <div class="editor"><?= formValue( 'editor_data' ) ?? $this->defaultText ?? '' ?></div>

    <?php }
}
