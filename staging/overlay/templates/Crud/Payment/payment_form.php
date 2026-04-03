<?php declare( strict_types=1 );

namespace App\View\Crud\Payment;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class payment_form extends AbstractView { public function show(): void {
    $payment = $this->payment;
    $isEdit  = $payment !== null;
    $action  = $isEdit
        ? routeLink( 'crud_payment_update', [ 'id' => $payment->getId() ] )
        : routeLink( 'crud_payment_store' );

    $fv          = static fn( string $k, mixed $entityVal ) => formValue( $k ) !== '' ? formValue( $k ) : ( $isEdit ? $entityVal : null );
    $boolOptions = [ '' => 'Null', '1' => 'True', '0' => 'False' ];
    $boolVal     = static fn( ?bool $v ) => $v === true ? '1' : ( $v === false ? '0' : '' );

    $statusOptions = [ 'pending' => 'Pending', 'completed' => 'Completed', 'failed' => 'Failed', 'refunded' => 'Refunded' ];
    $currentStatus = $fv( 'status', $payment?->getStatus() ) ?? 'pending';
    ?>
<!--@formatter:on-->
<div class="container mt-4" style="max-width: 760px;">

    <h1><?= $isEdit ? 'Edit Payment' : 'New Payment' ?></h1>

    <?php foreach ( getErrors() as $type => $error ): ?>
        <div class="alert alert-<?= is_string( $type ) ? htmlspecialchars( $type ) : 'danger' ?>">
            <?= htmlspecialchars( (string) $error ) ?>
        </div>
    <?php endforeach; ?>

    <form method="POST" action="<?= $action ?>">

        <h5 class="mt-3 mb-2 text-muted">Business Fields</h5>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="amount" class="form-label">Amount</label>
                <?= input( 'amount', [ 1, 15 ], 'number', true, [ 0.01, 9999999.99 ],
                    $fv( 'amount', $payment?->getAmount() ), '0.00', 0.01, [ 'form-control' ] ) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="currency" class="form-label">Currency</label>
                <?= input( 'currency', [ 3, 3 ], 'text', true, [],
                    $fv( 'currency', $payment?->getCurrency() ) ?? 'USD', 'USD', null,
                    [ 'form-control' ], [ 'text-transform' => 'uppercase' ] ) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select" required>
                    <?php foreach ( $statusOptions as $value => $label ): ?>
                        <option value="<?= $value ?>"<?= $currentStatus === $value ? ' selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr>
        <h5 class="mt-3 mb-2 text-muted">MariaDB Type Coverage</h5>

        <div class="mb-3">
            <label for="colText" class="form-label">colText <small class="text-muted">(text)</small></label>
            <?php formTextarea( 'colText', [ 0, 65535 ], 3, null, 'soft', false,
                $fv( 'colText', $payment?->getColText() ), null, [ 'form-control' ] ) ?>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="colInteger" class="form-label">colInteger</label>
                <?= input( 'colInteger', [ 1, 11 ], 'number', false, [], $fv( 'colInteger', $payment?->getColInteger() ), null, 1, [ 'form-control' ] ) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="colSmallint" class="form-label">colSmallint</label>
                <?= input( 'colSmallint', [ 1, 6 ], 'number', false, [], $fv( 'colSmallint', $payment?->getColSmallint() ), null, 1, [ 'form-control' ] ) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="colBigint" class="form-label">colBigint</label>
                <?= input( 'colBigint', [ 1, 20 ], 'number', false, [], $fv( 'colBigint', $payment?->getColBigint() ), null, 1, [ 'form-control' ] ) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="colDecimal" class="form-label">colDecimal <small class="text-muted">(15,4)</small></label>
                <?= input( 'colDecimal', [ 1, 20 ], 'number', false, [], $fv( 'colDecimal', $payment?->getColDecimal() ), null, 0.0001, [ 'form-control' ] ) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="colFloat" class="form-label">colFloat</label>
                <?= input( 'colFloat', [ 1, 20 ], 'number', false, [], $fv( 'colFloat', $payment?->getColFloat() ), null, 0.000001, [ 'form-control' ] ) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label for="colBoolean" class="form-label">colBoolean</label>
                <select id="colBoolean" name="colBoolean" class="form-select">
                    <?php $cur = $fv( 'colBoolean', $boolVal( $payment?->getColBoolean() ) ) ?? ''; ?>
                    <?php foreach ( $boolOptions as $val => $label ): ?>
                        <option value="<?= $val ?>"<?= (string) $cur === (string) $val ? ' selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="colDate" class="form-label">colDate</label>
                <?= input( 'colDate', [ 1, 10 ], 'date', false, [],
                    $fv( 'colDate', $payment?->getColDate()?->format( 'Y-m-d' ) ), null, null, [ 'form-control' ] ) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="colTime" class="form-label">colTime</label>
                <?= input( 'colTime', [ 1, 8 ], 'time', false, [],
                    $fv( 'colTime', $payment?->getColTime()?->format( 'H:i' ) ), null, null, [ 'form-control' ] ) ?>
            </div>
        </div>
        <div class="mb-3">
            <label for="colGuid" class="form-label">colGuid <small class="text-muted">(UUID)</small></label>
            <?= input( 'colGuid', [ 36, 36 ], 'text', false, [], $fv( 'colGuid', $payment?->getColGuid() ),
                'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', null, [ 'form-control' ] ) ?>
        </div>
        <div class="mb-3">
            <label for="colJson" class="form-label">colJson <small class="text-muted">(JSON)</small></label>
            <?php formTextarea( 'colJson', [ 0, 65535 ], 3, null, 'soft', false,
                $fv( 'colJson', $payment?->getColJson() !== null ? json_encode( $payment->getColJson(), JSON_PRETTY_PRINT ) : null ),
                '{"key": "value"}', [ 'form-control', 'font-monospace' ] ) ?>
        </div>
        <div class="mb-3">
            <label for="colArray" class="form-label">colArray <small class="text-muted">(JSON array)</small></label>
            <?php formTextarea( 'colArray', [ 0, 65535 ], 3, null, 'soft', false,
                $fv( 'colArray', $payment?->getColArray() !== null ? json_encode( $payment->getColArray(), JSON_PRETTY_PRINT ) : null ),
                htmlspecialchars('["item1", "item2"]'), [ 'form-control', 'font-monospace' ] ) ?>
        </div>
        <div class="mb-3">
            <label for="colSimpleArray" class="form-label">colSimpleArray <small class="text-muted">(comma-separated)</small></label>
            <?= input( 'colSimpleArray', [ 0, 1000 ], 'text', false, [],
                $fv( 'colSimpleArray', $payment?->getColSimpleArray() !== null ? implode( ', ', $payment->getColSimpleArray() ) : null ),
                'item1, item2', null, [ 'form-control' ] ) ?>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
            <a href="<?= routeLink( 'crud_payment_list' ) ?>" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

</div>
<!--@formatter:off-->
<?php } }
