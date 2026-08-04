<?php declare( strict_types=1 );

namespace App\View;

use PHP_SF\System\Classes\Abstracts\AbstractView;

/**
 * Demonstrates mixing all three rendering approaches on one page: this class
 * view is plain PHP and imports a Twig partial and a Blade partial.
 */
// @formatter:off
final class engines_mixed_page extends AbstractView { public function show(): void { ?>
  <!--@formatter:on-->

    <h2>Mixed engines on one page</h2>
    <p>This page body is a plain-PHP class view.</p>

    <?php $this->import('example/_partial.html.twig', htmlClassTagEnabled: false) ?>

    <?php $this->import('example/_partial.blade.php', htmlClassTagEnabled: false) ?>

    <!--@formatter:off-->
<?php } }
