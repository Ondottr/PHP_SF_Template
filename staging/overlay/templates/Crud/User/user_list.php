<?php declare( strict_types=1 );

namespace App\View\Crud\User;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class user_list extends AbstractView { public function show(): void { ?>
<!--@formatter:on-->
<div class="container-fluid mt-4">

    <?php foreach ( getMessages() as $type => $msg ): ?>
        <div class="alert alert-<?= is_string( $type ) ? htmlspecialchars( $type ) : 'success' ?>">
            <?= htmlspecialchars( (string) $msg ) ?>
        </div>
    <?php endforeach; ?>

    <?php foreach ( getErrors() as $type => $error ): ?>
        <div class="alert alert-<?= is_string( $type ) ? htmlspecialchars( $type ) : 'danger' ?>">
            <?= htmlspecialchars( (string) $error ) ?>
        </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Users</h1>
        <a href="<?= routeLink( 'crud_user_create' ) ?>" class="btn btn-primary">New User</a>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered table-hover table-sm" style="min-width: 1400px;">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>colText</th>
                <th>colInteger</th>
                <th>colSmallint</th>
                <th>colBigint</th>
                <th>colBoolean</th>
                <th>colDecimal</th>
                <th>colFloat</th>
                <th>colDatetimetz</th>
                <th>colDate</th>
                <th>colTime</th>
                <th>colGuid</th>
                <th>colJson</th>
                <th>colArray</th>
                <th>colSimpleArray</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $this->users as $user ): ?>
            <tr>
                <td><?= $user->getId() ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $user->getEmail() ?? '' ) ?>"><?= htmlspecialchars( $user->getEmail() ?? '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $user->getColText() ?? '' ) ?>"><?= htmlspecialchars( $user->getColText() ?? '' ) ?></td>
                <td><?= $user->getColInteger() ?? '' ?></td>
                <td><?= $user->getColSmallint() ?? '' ?></td>
                <td><?= $user->getColBigint() ?? '' ?></td>
                <td><?= $user->getColBoolean() === null ? '' : ( $user->getColBoolean() ? 'true' : 'false' ) ?></td>
                <td><?= $user->getColDecimal() ?? '' ?></td>
                <td><?= $user->getColFloat() ?? '' ?></td>
                <td><?= $user->getColDatetimetz()?->format( 'Y-m-d H:i' ) ?? '' ?></td>
                <td><?= $user->getColDate()?->format( 'Y-m-d' ) ?? '' ?></td>
                <td><?= $user->getColTime()?->format( 'H:i' ) ?? '' ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $user->getColGuid() ?? '' ) ?>"><?= htmlspecialchars( $user->getColGuid() ?? '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $user->getColJson() !== null ? json_encode( $user->getColJson() ) : '' ) ?>"><?= htmlspecialchars( $user->getColJson() !== null ? json_encode( $user->getColJson() ) : '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $user->getColArray() !== null ? json_encode( $user->getColArray() ) : '' ) ?>"><?= htmlspecialchars( $user->getColArray() !== null ? json_encode( $user->getColArray() ) : '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $user->getColSimpleArray() !== null ? implode( ', ', $user->getColSimpleArray() ) : '' ) ?>"><?= htmlspecialchars( $user->getColSimpleArray() !== null ? implode( ', ', $user->getColSimpleArray() ) : '' ) ?></td>
                <td><?= $user->getCreatedAt()->format( 'Y-m-d H:i' ) ?></td>
                <td class="text-nowrap">
                    <a href="<?= routeLink( 'crud_user_edit', [ 'id' => $user->getId() ] ) ?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="<?= routeLink( 'crud_user_delete', [ 'id' => $user->getId() ] ) ?>" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm( 'Delete this user?' )">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if ( empty( $this->users ) ): ?>
            <tr>
                <td colspan="18" class="text-center text-muted">No users found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>
<!--@formatter:off-->
<?php } }
