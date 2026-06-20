<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$userId = (int) ($_GET['id'] ?? 0);
$profileUser = $userId > 0 ? public_user_profile($userId) : null;
$backTripId = (int) ($_GET['trip_id'] ?? 0);
$backTrip = $backTripId > 0 ? find_trip($backTripId) : null;

if (!$profileUser) {
    set_flash('error', 'No hemos encontrado ese perfil.');
    redirect_to(app_url('/trips/viajes.php'));
}

$pageTitle = 'Perfil de ' . (string) ($profileUser['full_name'] ?? 'usuario');
$ratingSummary = user_rating_summary((int) $profileUser['id']);
$publishedTrips = public_user_trip_cards((int) $profileUser['id']);
$receivedReviews = public_user_received_reviews((int) $profileUser['id']);
$upcomingTrips = array_values(array_filter($publishedTrips, static fn (array $trip): bool => strtotime((string) $trip['departure_at']) >= time()));
$pastTrips = array_values(array_filter($publishedTrips, static fn (array $trip): bool => strtotime((string) $trip['departure_at']) < time()));
$roundedAverage = (int) round((float) $ratingSummary['average']);

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Perfil publico</h1>
        <p class="muted">Consulta la informacion basica del conductor, sus viajes publicados y las opiniones recibidas.</p>
    </div>
    <div class="toolbar">
        <?php if ($backTrip): ?>
            <a class="button button-primary" href="<?= app_url('/trips/viaje.php?id=' . (int) $backTrip['id']) ?>">Volver al viaje</a>
        <?php endif; ?>
        <a class="button button-ghost" href="<?= app_url('/trips/viajes.php') ?>">Volver a viajes</a>
    </div>
</section>

<section class="profile-shell public-profile-shell">
    <article class="card profile-card">
        <div class="card-body">
            <div class="public-profile-head">
                <div>
                    <span class="detail-eyebrow"><?= e((string) ($profileUser['role'] ?? 'user')) ?></span>
                    <h2><?= e((string) $profileUser['full_name']) ?></h2>
                    <p class="muted">Miembro desde <?= e(date('d/m/Y', strtotime((string) ($profileUser['created_at'] ?? 'now')))) ?></p>
                </div>
                <div class="public-profile-rating">
                    <strong><?= $ratingSummary['count'] > 0 ? e((string) $ratingSummary['average']) . '/5' : 'Sin valoraciones' ?></strong>
                    <div class="rating-stars-view" aria-label="Valoracion media <?= e((string) $ratingSummary['average']) ?> de 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="rating-star<?= $i <= $roundedAverage ? ' is-filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="muted"><?= (int) $ratingSummary['count'] ?> opiniones recibidas</span>
                    <a class="public-profile-rating-link" href="<?= app_url('/account/opiniones-usuario.php?id=' . (int) $profileUser['id'] . ($backTrip ? '&trip_id=' . (int) $backTrip['id'] : '')) ?>">Ver todas las opiniones</a>
                </div>
            </div>

            <div class="trip-driver-grid public-profile-meta">
                <div>
                    <span class="trip-driver-label">Correo</span>
                    <strong><?= !empty($profileUser['email']) ? e((string) $profileUser['email']) : 'No disponible' ?></strong>
                </div>
                <div>
                    <span class="trip-driver-label">Telefono</span>
                    <strong><?= !empty($profileUser['phone']) ? e((string) $profileUser['phone']) : 'No disponible' ?></strong>
                </div>
                <div>
                    <span class="trip-driver-label">Viajes proximos</span>
                    <strong><?= count($upcomingTrips) ?></strong>
                </div>
                <div>
                    <span class="trip-driver-label">Viajes pasados</span>
                    <strong><?= count($pastTrips) ?></strong>
                </div>
            </div>
        </div>
    </article>

    <aside class="card profile-side-card">
        <div class="card-body">
            <h2>Resumen</h2>
            <div class="meta-row">
                <span class="pill"><?= count($publishedTrips) ?> viajes publicados</span>
                <span class="pill"><?= (int) $ratingSummary['count'] ?> opiniones</span>
                <?php if ($ratingSummary['count'] > 0): ?>
                    <span class="pill"><?= e((string) $ratingSummary['average']) ?>/5 media</span>
                <?php endif; ?>
            </div>
            <p class="muted">Este perfil muestra informacion orientada a dar confianza antes de reservar un viaje con este conductor.</p>
        </div>
    </aside>
</section>

<?php if ($publishedTrips): ?>
    <section class="reservation-history-group">
        <div class="section-head">
            <div>
                <h2>Viajes publicados</h2>
                <p class="muted"><?= count($publishedTrips) ?> viajes visibles en el perfil.</p>
            </div>
        </div>
        <section class="cards-grid">
            <?php foreach ($publishedTrips as $trip): ?>
                <?php $driverSummary = !empty($trip['creator_user_id']) ? user_rating_summary((int) $trip['creator_user_id']) : ['count' => 0, 'average' => 0]; ?>
                <?php $driverRoundedAverage = (int) round((float) $driverSummary['average']); ?>
                <article class="card trip-card">
                    <a class="trip-card-media-link" href="<?= e(trip_detail_url($trip)) ?>">
                        <img class="trip-cover" src="<?= e(destination_image($trip['destination'] ?? null, $trip['image_path'] ?? null)) ?>" alt="<?= e((string) $trip['name']) ?>">
                    </a>
                    <div class="card-body">
                        <h3><a class="trip-card-title-link" href="<?= e(trip_detail_url($trip)) ?>"><?= e((string) $trip['name']) ?></a></h3>
                        <p class="muted"><?= e((string) $trip['origin']) ?> → <?= e((string) $trip['destination']) ?></p>
                        <div class="trip-card-driver">
                            <div class="trip-card-driver-main">
                                <span class="trip-card-driver-label">Conductor</span>
                                <?php if (!empty($trip['creator_user_id'])): ?>
                                    <a class="trip-card-driver-name" href="<?= app_url('/account/usuario.php?id=' . (int) $trip['creator_user_id'] . '&trip_id=' . (int) $trip['id']) ?>"><?= e(trip_driver_name($trip)) ?></a>
                                <?php else: ?>
                                    <strong class="trip-card-driver-name static"><?= e(trip_driver_name($trip)) ?></strong>
                                <?php endif; ?>
                            </div>
                            <?php if ($driverSummary['count'] > 0): ?>
                                <div class="trip-card-driver-rating">
                                    <div class="rating-stars-view rating-stars-view-compact" aria-label="Valoracion media <?= e((string) $driverSummary['average']) ?> de 5">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="rating-star<?= $i <= $driverRoundedAverage ? ' is-filled' : '' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="trip-card-driver-score"><?= e((string) $driverSummary['average']) ?>/5</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="meta-row">
                            <span class="pill"><?= e(format_datetime((string) $trip['departure_at'])) ?></span>
                            <span class="pill"><?= remaining_seats($trip) ?> plazas libres</span>
                            <span class="pill"><?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
<?php endif; ?>

<section class="reservation-history-group">
    <div class="section-head">
        <div>
            <h2>Opiniones recibidas</h2>
            <p class="muted"><?= (int) $ratingSummary['count'] ?> valoraciones registradas.</p>
        </div>
        <?php if ($ratingSummary['count'] > 0): ?>
            <a class="button button-ghost" href="<?= app_url('/account/opiniones-usuario.php?id=' . (int) $profileUser['id'] . ($backTrip ? '&trip_id=' . (int) $backTrip['id'] : '')) ?>">Todas las opiniones</a>
        <?php endif; ?>
    </div>

    <?php if (!$receivedReviews): ?>
        <article class="card empty-state">
            <div class="card-body">Todavia no hay opiniones publicas para este perfil.</div>
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
                                    <span class="pill"><?= (int) $review['rating'] ?>/5</span>
                                    <span class="pill"><?= e((string) $review['reviewer_role'] === 'driver' ? 'Opinion de conductor' : 'Opinion de pasajero') ?></span>
                                    <span class="pill"><?= e(format_datetime((string) $review['updated_at'])) ?></span>
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
