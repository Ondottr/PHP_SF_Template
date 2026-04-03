<?php declare( strict_types=1 );

namespace App\View;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class crud_home extends AbstractView { public function show(): void { ?>
<!--@formatter:on-->
<div class="container mt-5">

    <h1 class="mb-1">CRUD Home</h1>
    <p class="text-muted mb-5">Choose a section to manage entities.</p>

    <div class="row g-4">

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">PHP_SF Framework</h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small mb-3">
                        Custom routing &amp; PHP template views. CSRF handled by the framework lifecycle.
                    </p>
                    <div class="list-group">
                        <a href="<?= routeLink( 'crud_user_list' ) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Users
                            <span class="badge bg-primary rounded-pill">PostgreSQL</span>
                        </a>
                        <a href="<?= routeLink( 'crud_post_list' ) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Posts
                            <span class="badge bg-warning text-dark rounded-pill">MySQL</span>
                        </a>
                        <a href="<?= routeLink( 'crud_payment_list' ) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Payments
                            <span class="badge bg-info text-dark rounded-pill">MariaDB</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Symfony / Twig</h5>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small mb-3">
                        Symfony attribute routing &amp; Twig templates. CSRF validated manually per request.
                    </p>
                    <div class="list-group">
                        <a href="<?= routeLink( 'symfony_crud_user_list' ) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Users
                            <span class="badge bg-primary rounded-pill">PostgreSQL</span>
                        </a>
                        <a href="<?= routeLink( 'symfony_crud_post_list' ) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Posts
                            <span class="badge bg-warning text-dark rounded-pill">MySQL</span>
                        </a>
                        <a href="<?= routeLink( 'symfony_crud_payment_list' ) ?>"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Payments
                            <span class="badge bg-info text-dark rounded-pill">MariaDB</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!--@formatter:off-->
<?php } }
