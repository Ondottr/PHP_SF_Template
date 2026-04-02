<?php declare( strict_types=1 );

namespace App\View\Crud\Payment;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class payment_list extends AbstractView { public function show(): void { ?>
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
        <h1>Payments</h1>
        <a href="<?= routeLink( 'crud_payment_create' ) ?>" class="btn btn-primary">New Payment</a>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered table-hover table-sm" style="min-width: 1700px;">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Status</th>
                <th>colText</th>
                <th>colInteger</th>
                <th>colSmallint</th>
                <th>colBigint</th>
                <th>colBoolean</th>
                <th>colDecimal</th>
                <th>colFloat</th>
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
            <?php foreach ( $this->payments as $payment ): ?>
            <tr>
                <td><?= $payment->getId() ?></td>
                <td><?= number_format( (float) $payment->getAmount(), 2 ) ?></td>
                <td><?= htmlspecialchars( $payment->getCurrency() ) ?></td>
                <td>
                    <span class="badge bg-<?= match ( $payment->getStatus() ) {
                        'completed' => 'success',
                        'failed'    => 'danger',
                        'refunded'  => 'secondary',
                        default     => 'warning text-dark',
                    } ?>">
                        <?= htmlspecialchars( $payment->getStatus() ) ?>
                    </span>
                </td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $payment->getColText() ?? '' ) ?>"><?= htmlspecialchars( $payment->getColText() ?? '' ) ?></td>
                <td><?= $payment->getColInteger() ?? '' ?></td>
                <td><?= $payment->getColSmallint() ?? '' ?></td>
                <td><?= $payment->getColBigint() ?? '' ?></td>
                <td><?= $payment->getColBoolean() === null ? '' : ( $payment->getColBoolean() ? 'true' : 'false' ) ?></td>
                <td><?= $payment->getColDecimal() ?? '' ?></td>
                <td><?= $payment->getColFloat() ?? '' ?></td>
                <td><?= $payment->getColDate()?->format( 'Y-m-d' ) ?? '' ?></td>
                <td><?= $payment->getColTime()?->format( 'H:i' ) ?? '' ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $payment->getColGuid() ?? '' ) ?>"><?= htmlspecialchars( $payment->getColGuid() ?? '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $payment->getColJson() !== null ? json_encode( $payment->getColJson() ) : '' ) ?>"><?= htmlspecialchars( $payment->getColJson() !== null ? json_encode( $payment->getColJson() ) : '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $payment->getColArray() !== null ? json_encode( $payment->getColArray() ) : '' ) ?>"><?= htmlspecialchars( $payment->getColArray() !== null ? json_encode( $payment->getColArray() ) : '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $payment->getColSimpleArray() !== null ? implode( ', ', $payment->getColSimpleArray() ) : '' ) ?>"><?= htmlspecialchars( $payment->getColSimpleArray() !== null ? implode( ', ', $payment->getColSimpleArray() ) : '' ) ?></td>
                <td><?= $payment->getCreatedAt()->format( 'Y-m-d H:i' ) ?></td>
                <td class="text-nowrap">
                    <a href="<?= routeLink( 'crud_payment_edit', [ 'id' => $payment->getId() ] ) ?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="<?= routeLink( 'crud_payment_delete', [ 'id' => $payment->getId() ] ) ?>" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm( 'Delete this payment?' )">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if ( empty( $this->payments ) ): ?>
            <tr>
                <td colspan="19" class="text-center text-muted">No payments found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>
<!--@formatter:off-->
<?php } }
