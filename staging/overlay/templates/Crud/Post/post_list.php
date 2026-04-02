<?php declare( strict_types=1 );

namespace App\View\Crud\Post;

use PHP_SF\System\Classes\Abstracts\AbstractView;

// @formatter:off
final class post_list extends AbstractView { public function show(): void { ?>
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
        <h1>Posts</h1>
        <a href="<?= routeLink( 'crud_post_create' ) ?>" class="btn btn-primary">New Post</a>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered table-hover table-sm" style="min-width: 1600px;">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Content</th>
                <th>Status</th>
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
            <?php foreach ( $this->posts as $post ): ?>
            <tr>
                <td><?= $post->getId() ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $post->getTitle() ?? '' ) ?>"><?= htmlspecialchars( $post->getTitle() ?? '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $post->getContent() ?? '' ) ?>"><?= htmlspecialchars( $post->getContent() ?? '' ) ?></td>
                <td>
                    <span class="badge bg-<?= match ( $post->getStatus() ) {
                        'published' => 'success',
                        'archived'  => 'secondary',
                        default     => 'warning text-dark',
                    } ?>">
                        <?= htmlspecialchars( $post->getStatus() ) ?>
                    </span>
                </td>
                <td><?= $post->getColInteger() ?? '' ?></td>
                <td><?= $post->getColSmallint() ?? '' ?></td>
                <td><?= $post->getColBigint() ?? '' ?></td>
                <td><?= $post->getColBoolean() === null ? '' : ( $post->getColBoolean() ? 'true' : 'false' ) ?></td>
                <td><?= $post->getColDecimal() ?? '' ?></td>
                <td><?= $post->getColFloat() ?? '' ?></td>
                <td><?= $post->getColDate()?->format( 'Y-m-d' ) ?? '' ?></td>
                <td><?= $post->getColTime()?->format( 'H:i' ) ?? '' ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $post->getColGuid() ?? '' ) ?>"><?= htmlspecialchars( $post->getColGuid() ?? '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $post->getColJson() !== null ? json_encode( $post->getColJson() ) : '' ) ?>"><?= htmlspecialchars( $post->getColJson() !== null ? json_encode( $post->getColJson() ) : '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $post->getColArray() !== null ? json_encode( $post->getColArray() ) : '' ) ?>"><?= htmlspecialchars( $post->getColArray() !== null ? json_encode( $post->getColArray() ) : '' ) ?></td>
                <td class="cell-trunc" title="<?= htmlspecialchars( $post->getColSimpleArray() !== null ? implode( ', ', $post->getColSimpleArray() ) : '' ) ?>"><?= htmlspecialchars( $post->getColSimpleArray() !== null ? implode( ', ', $post->getColSimpleArray() ) : '' ) ?></td>
                <td><?= $post->getCreatedAt()->format( 'Y-m-d H:i' ) ?></td>
                <td class="text-nowrap">
                    <a href="<?= routeLink( 'crud_post_edit', [ 'id' => $post->getId() ] ) ?>" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="<?= routeLink( 'crud_post_delete', [ 'id' => $post->getId() ] ) ?>" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm( 'Delete this post?' )">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if ( empty( $this->posts ) ): ?>
            <tr>
                <td colspan="18" class="text-center text-muted">No posts found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>
<!--@formatter:off-->
<?php } }
