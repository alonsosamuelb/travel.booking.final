<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Mis viajes';
$trips = fetch_trips([], true, (int) current_user()['id']);

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Mis viajes</h1>
        <p class="muted">Gestiona tus publicaciones de forma sencilla: publicar, pasar a borrador o eliminar si no tienen reservas activas.</p>
    </div>
    <a class="button button-primary" href="<?= app_url('/trips/publicar-viaje.php') ?>">Nuevo viaje</a>
</section>

<?php if (!$trips): ?>
    <article class="card empty-state">
        <div class="card-body">Todavia no has publicado viajes.</div>
    </article>
<?php else: ?>
    <section class="cards-grid">
        <?php foreach ($trips as $trip): ?>
            <?php $driverSummary = !empty($trip['creator_user_id']) ? user_rating_summary((int) $trip['creator_user_id']) : ['count' => 0, 'average' => 0]; ?>
            <?php $driverRoundedAverage = (int) round((float) $driverSummary['average']); ?>
            <article class="card trip-card">
                <a class="trip-card-media-link" href="<?= e(trip_detail_url($trip)) ?>">
                    <img class="trip-cover" src="<?= e(destination_image($trip['destination'] ?? null, $trip['image_path'] ?? null)) ?>" alt="<?= e($trip['name']) ?>">
                </a>
                <div class="card-body">
                    <h3><a class="trip-card-title-link" href="<?= e(trip_detail_url($trip)) ?>"><?= e($trip['name']) ?></a></h3>
                    <p class="muted"><?= e($trip['origin']) ?> → <?= e($trip['destination']) ?></p>
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
                        <span class="pill"><?= e(format_datetime($trip['departure_at'])) ?></span>
                        <span class="pill"><?= e($trip['vehicle']) ?></span>
                        <span class="pill"><?= remaining_seats($trip) ?> plazas libres</span>
                        <span class="pill price-pill"><?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?></span>
                        <span class="pill status-<?= e($trip['status']) ?>"><?= e(trip_status_label($trip['status'])) ?></span>
                    </div>
                    <div class="toolbar">
                        <a class="button button-primary" href="<?= app_url('/trips/editar-viaje.php?id=' . (int) $trip['id']) ?>">Editar</a>
                        <form method="post" action="<?= app_url('/actions/toggle_trip_status.php') ?>">
                            <input type="hidden" name="trip_id" value="<?= (int) $trip['id'] ?>">
                            <input type="hidden" name="action_name" value="toggle">
                            <button class="button button-ghost" type="submit">
                                <?= $trip['status'] === 'published' ? 'Pasar a borrador' : 'Publicar' ?>
                            </button>
                        </form>
                        <form method="post" action="<?= app_url('/actions/toggle_trip_status.php') ?>" onsubmit="return confirm('Se eliminara el viaje si no tiene reservas activas.');">
                            <input type="hidden" name="trip_id" value="<?= (int) $trip['id'] ?>">
                            <input type="hidden" name="action_name" value="delete">
                            <button class="button button-danger" type="submit">Eliminar</button>
                        </form>
                        <?php if (strtotime((string) $trip['departure_at']) < time()): ?>
                            <a class="button button-ghost" href="<?= app_url('/trips/valorar-viajeros.php?trip_id=' . (int) $trip['id']) ?>">Valorar pasajeros</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
