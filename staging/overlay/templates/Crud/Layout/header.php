<?php declare( strict_types=1 );

namespace App\View\Crud\Layout;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class header extends AbstractView { public function show(): void {
    ?>
    <!--@formatter:on-->
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?= pageTitle() ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
    <!--@formatter:off-->
<?php } }
