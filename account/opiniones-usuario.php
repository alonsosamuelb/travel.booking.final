<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$userId = (int) ($_GET['id'] ?? 0);
$backTripId = (int) ($_GET['trip_id'] ?? 0);
$profileUser = $userId > 0 ? public_user_profile($userId) : null;
$backTrip = $backTripId > 0 ? find_trip($backTripId) : null;

if (!$profileUser) {
    set_flash('error', 'No hemos encontrado ese perfil.');
    redirect_to(app_url('/trips/viajes.php'));
}

$pageTitle = 'Opiniones de ' . (string) ($profileUser['full_name'] ?? 'usuario');
$ratingSummary = user_rating_summary((int) $profileUser['id']);
$ratingBreakdown = user_rating_breakdown((int) $profileUser['id']);
$receivedReviews = public_user_received_reviews((int) $profileUser['id'], 30);
$ratingLabels = [
    5 => 'Excelente',
    4 => 'Muy bien',
    3 => 'Correcto',
    2 => 'Mal',
    1 => 'Muy mal',
];
$roundedAverage = (int) round((float) $ratingSummary['average']);

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Todas las opiniones</h1>
        <p class="muted">Resumen completo de valoraciones recibidas por <?= e((string) $profileUser['full_name']) ?>.</p>
    </div>
    <div class="toolbar">
        <?php if ($backTrip): ?>
            <a class="button button-primary" href="<?= app_url('/trips/viaje.php?id=' . (int) $backTrip['id']) ?>">Volver al viaje</a>
        <?php endif; ?>
        <a class="button button-ghost" href="<?= app_url('/account/usuario.php?id=' . (int) $profileUser['id'] . ($backTrip ? '&trip_id=' . (int) $backTrip['id'] : '')) ?>">Volver al perfil</a>
    </div>
</section>

<section class="profile-shell public-profile-shell">
    <article class="card profile-card">
        <div class="card-body">
            <div class="public-profile-head">
                <div>
                    <span class="detail-eyebrow">Opiniones</span>
                    <h2><?= $ratingSummary['count'] > 0 ? e((string) $ratingSummary['average']) . '/5' : 'Sin valoraciones' ?></h2>
                    <p class="muted"><?= (int) $ratingSummary['count'] ?> opiniones registradas</p>
                </div>
                <div class="public-profile-rating">
                    <strong><?= e((string) $profileUser['full_name']) ?></strong>
                    <div class="rating-stars-view" aria-label="Valoracion media <?= e((string) $ratingSummary['average']) ?> de 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="rating-star<?= $i <= $roundedAverage ? ' is-filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="muted"><?= $ratingSummary['count'] > 0 ? e((string) $ratingSummary['average']) . '/5 de media' : 'Todavia sin valoraciones' ?></span>
                </div>
            </div>

            <div class="review-breakdown-list">
                <?php foreach ($ratingLabels as $rating => $label): ?>
                    <div class="review-breakdown-row">
                        <span><?= e($label) ?></span>
                        <strong><?= (int) ($ratingBreakdown[$rating] ?? 0) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </article>

    <aside class="card profile-side-card">
        <div class="card-body">
            <h2>Resumen</h2>
            <div class="meta-row">
                <span class="pill"><?= (int) $ratingSummary['count'] ?> opiniones</span>
                <?php if ($ratingSummary['count'] > 0): ?>
                    <span class="pill"><?= e((string) $ratingSummary['average']) ?>/5 media</span>
                <?php endif; ?>
            </div>
            <p class="muted">Las opiniones muestran valoraciones cruzadas entre pasajeros y conductores en viajes completados.</p>
        </div>
    </aside>
</section>

<section class="reservation-history-group">
    <div class="section-head">
        <div>
            <h2>Listado de opiniones</h2>
            <p class="muted"><?= (int) $ratingSummary['count'] ?> opiniones visibles.</p>
        </div>
    </div>

    <?php if (!$receivedReviews): ?>
        <article class="card empty-state">
            <div class="card-body">Todavia no hay opiniones publicas para este usuario.</div>
        </article>
    <?php else: ?>
        <section class="card admin-panel-card">
            <div class="card-body">
                <div class="admin-list">
                    <?php foreach ($receivedReviews as $review): ?>
                        <article class="admin-row">
                            <div class="admin-row-main">
                                <div class="review-card-head">
                                    <?php if (!empty($review['reviewer_id'])): ?>
                                        <a class="review-author-link" href="<?= app_url('/account/usuario.php?id=' . (int) $review['reviewer_id'] . (!empty($review['trip_id']) ? '&trip_id=' . (int) $review['trip_id'] : ($backTrip ? '&trip_id=' . (int) $backTrip['id'] : ''))) ?>">
                                            <strong><?= e((string) $review['reviewer_name']) ?></strong>
                                            <span class="review-author-link-hint">Ver perfil publico</span>
                                            <span class="review-author-link-arrow" aria-hidden="true">→</span>
                                        </a>
                                    <?php else: ?>
                                        <strong><?= e((string) $review['reviewer_name']) ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($review['reviewer_id'])): ?>
                                        <a class="button button-ghost button-compact" href="<?= app_url('/account/usuario.php?id=' . (int) $review['reviewer_id'] . (!empty($review['trip_id']) ? '&trip_id=' . (int) $review['trip_id'] : ($backTrip ? '&trip_id=' . (int) $backTrip['id'] : ''))) ?>">Ver perfil</a>
                                    <?php endif; ?>
                                </div>
                                <span class="muted"><?= e((string) $review['trip_name']) ?> · <?= e((string) $review['origin']) ?> → <?= e((string) $review['destination']) ?></span>
                                <div class="meta-row">
                                    <span class="pill"><?= e($ratingLabels[(int) $review['rating']] ?? ((int) $review['rating'] . '/5')) ?></span>
                                    <span class="pill"><?= (int) $review['rating'] ?>/5</span>
                                    <span class="pill"><?= e((string) $review['reviewer_role'] === 'driver' ? 'Opinion de conductor' : 'Opinion de pasajero') ?></span>
                                    <span class="pill"><?= e(date('m/Y', strtotime((string) $review['updated_at']))) ?></span>
                                </div>
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="muted"><?= e((string) $review['comment']) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
