<?php declare( strict_types=1 );

namespace App\View\Components\CKEditor;

use App\Kernel;
use PHP_SF\System\Classes\Abstracts\AbstractView;

/**
 * @property string defaultText
 */
// @formatter:off
final class editor_component extends AbstractView { public function show(): void { ?>
  <!--@formatter:on-->

  <?php Kernel::setEditorStatus( true ) ?>

  <input id="editor_data" type="hidden" name="editor_data">
  <div class="editor"><?= formValue( 'editor_data' ) ?? $this->defaultText ?? '' ?></div>

  <!--@formatter:off-->
<?php } }
