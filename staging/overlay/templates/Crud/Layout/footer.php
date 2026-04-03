<?php declare( strict_types=1 );

namespace App\View\Crud\Layout;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class footer extends AbstractView { public function show(): void {
    ?>
    <!--@formatter:on-->

    <script src="<?= asset( 'js/bootstrap.min.js' ) ?>"></script>
    </body>
    </html>
<?php } }
