<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$tripId = (int) ($_GET['id'] ?? 0);
$trip = $tripId > 0 ? find_trip($tripId) : null;

if (!$trip) {
    set_flash('error', 'No hemos encontrado ese viaje.');
    redirect_to(app_url('/trips/viajes.php'));
}

$pageTitle = $trip['name'] ?: 'Detalle del viaje';
$viewer = current_user();
$viewerIsAdmin = $viewer && (($viewer['role'] ?? '') === 'admin');
$isOwner = $viewer && (int) ($trip['creator_user_id'] ?? 0) === (int) $viewer['id'];
$hasPriceDefined = (float) ($trip['price_per_seat'] ?? 0) > 0;
$isBookable = $trip['status'] === 'published' && strtotime((string) $trip['departure_at']) > time() && remaining_seats($trip) > 0 && $hasPriceDefined;
$reservationRules = $viewer && !$viewerIsAdmin ? user_active_reservation_rules((int) $viewer['id']) : null;
$tripDateKey = reservation_date_key((string) $trip['departure_at']);
$tripWeekKey = reservation_week_key((string) $trip['departure_at']);
$tripVehicleGroup = vehicle_group_from_vehicle((string) $trip['vehicle']);
$driverReviewSummary = !empty($trip['creator_user_id']) ? user_rating_summary((int) $trip['creator_user_id']) : ['count' => 0, 'average' => 0];
$driverRoundedAverage = (int) round((float) $driverReviewSummary['average']);

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="trip-detail-shell">
    <div class="trip-detail-back">
        <a href="<?= app_url('/trips/viajes.php') ?>">← Volver a viajes</a>
    </div>

    <div class="trip-detail-grid">
        <article class="card trip-detail-media">
            <img class="trip-detail-cover" src="<?= e(destination_image($trip['destination'] ?? null, $trip['image_path'] ?? null)) ?>" alt="<?= e($trip['name']) ?>">
        </article>

        <article class="card trip-detail-main">
            <div class="card-body">
                <span class="detail-eyebrow"><?= e($trip['vehicle']) ?></span>
                <h1><?= e($trip['name']) ?></h1>
                <p class="trip-detail-route"><?= e($trip['origin']) ?> → <?= e($trip['destination']) ?></p>

                <div class="meta-row trip-detail-meta">
                    <span class="pill"><?= e(format_datetime($trip['departure_at'])) ?></span>
                    <span class="pill"><?= remaining_seats($trip) ?> plazas libres</span>
                    <span class="pill price-pill"><?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?></span>
                    <span class="pill status-<?= e($trip['status']) ?>"><?= e(trip_status_label($trip['status'])) ?></span>
                </div>

                <div class="trip-detail-copy">
                    <p><?= nl2br(e($trip['description'] ?: 'Sin descripcion adicional.')) ?></p>
                </div>

                <div class="notice success">
                    El conductor ha marcado un precio orientativo de <?= e(format_money((float) ($trip['price_per_seat'] ?? 0))) ?> por plaza. Para reservar en la app solo se paga una cuota online de 1,00 €. El resto se abona directamente al conductor por <?= e(off_platform_payment_methods_label()) ?>.
                </div>

                <div class="trip-driver-card">
                    <h2>Conductor</h2>
                    <div class="trip-driver-grid">
                        <div>
                            <span class="trip-driver-label">Nombre</span>
                            <strong>
                                <?php if (!empty($trip['creator_user_id'])): ?>
                                    <a class="trip-driver-link" href="<?= app_url('/account/usuario.php?id=' . (int) $trip['creator_user_id'] . '&trip_id=' . (int) $trip['id']) ?>"><?= e(trip_driver_name($trip)) ?></a>
                                <?php else: ?>
                                    <?= e(trip_driver_name($trip)) ?>
                                <?php endif; ?>
                            </strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Contacto</span>
                            <strong><?= !empty($trip['creator_phone']) ? e((string) $trip['creator_phone']) : 'No disponible' ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Email</span>
                            <strong><?= !empty($trip['creator_email']) ? e((string) $trip['creator_email']) : 'No disponible' ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Tipo de vehiculo</span>
                            <strong><?= stripos((string) ($trip['vehicle'] ?? ''), 'van') !== false ? 'Van' : 'Coche' ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Valoracion</span>
                            <strong>
                                <?php if (!empty($trip['creator_user_id']) && $driverReviewSummary['count'] > 0): ?>
                                    <a class="trip-driver-link" href="<?= app_url('/account/usuario.php?id=' . (int) $trip['creator_user_id'] . '&trip_id=' . (int) $trip['id']) ?>"><?= e((string) $driverReviewSummary['average']) . '/5 (' . (int) $driverReviewSummary['count'] . ')' ?></a>
                                <?php else: ?>
                                    <?= $driverReviewSummary['count'] > 0 ? e((string) $driverReviewSummary['average']) . '/5 (' . (int) $driverReviewSummary['count'] . ')' : 'Sin valoraciones' ?>
                                <?php endif; ?>
                            </strong>
                            <?php if ($driverReviewSummary['count'] > 0): ?>
                                <div class="rating-stars-view trip-driver-stars" aria-label="Valoracion media <?= e((string) $driverReviewSummary['average']) ?> de 5">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="rating-star<?= $i <= $driverRoundedAverage ? ' is-filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($trip['creator_user_id'])): ?>
                        <div class="toolbar" style="margin-top: 16px;">
                            <a class="button button-ghost" href="<?= app_url('/account/usuario.php?id=' . (int) $trip['creator_user_id'] . '&trip_id=' . (int) $trip['id']) ?>">Ver perfil del conductor</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="trip-detail-actions">
                    <?php if ($isOwner): ?>
                        <a class="button button-primary" href="<?= app_url('/trips/editar-viaje.php?id=' . (int) $trip['id']) ?>">Gestionar mi viaje</a>
                    <?php elseif (!is_logged_in()): ?>
                        <a class="button button-primary" href="<?= app_url('/auth/login.php') ?>">Inicia sesion para reservar</a>
                    <?php elseif ($isBookable): ?>
                        <form
                            class="trip-booking-form"
                            method="post"
                            action="<?= app_url('/actions/create_reservation.php') ?>"
                            data-reservation-form
                            data-trip-date="<?= e($tripDateKey) ?>"
                            data-trip-week="<?= e($tripWeekKey) ?>"
                            data-trip-vehicle-group="<?= e($tripVehicleGroup) ?>"
                            data-admin="<?= $viewerIsAdmin ? '1' : '0' ?>"
                            data-daily-limit="2"
                            data-weekly-seat-limit="4"
                            data-same-day-count="<?= (int) (($reservationRules['same_day_counts'][$tripDateKey] ?? 0)) ?>"
                            data-same-vehicle-week-count="<?= (int) (($reservationRules['vehicle_week_counts'][$tripVehicleGroup . '|' . $tripWeekKey] ?? 0)) ?>"
                            data-weekly-seats="<?= (int) (($reservationRules['weekly_seats'][$tripWeekKey] ?? 0)) ?>"
                        >
                            <input type="hidden" name="trip_id" value="<?= (int) $trip['id'] ?>">
                            <input type="hidden" name="travel_role" value="passenger">
                            <label class="trip-booking-field trip-booking-seats">
                                <span>Plazas</span>
                                <div class="seat-stepper" data-stepper>
                                    <button class="seat-stepper-button" type="button" data-stepper-decrease aria-label="Restar una plaza"><span class="seat-stepper-glyph">−</span></button>
                                    <input class="seat-stepper-input" type="number" name="seats_reserved" min="1" max="<?= remaining_seats($trip) ?>" value="1" required readonly inputmode="numeric" aria-label="Numero de plazas">
                                    <button class="seat-stepper-button" type="button" data-stepper-increase aria-label="Sumar una plaza"><span class="seat-stepper-glyph">+</span></button>
                                </div>
                            </label>
                            <label class="trip-booking-field notes">
                                <span>Nota para el conductor</span>
                                <textarea name="notes" maxlength="500" placeholder="Ejemplo: llevo una maleta."></textarea>
                            </label>
                            <button class="button button-primary trip-booking-submit" type="submit">Reservar este viaje</button>
                        </form>
                        <div class="trip-booking-rules muted">
                            <?php if ($viewerIsAdmin): ?>
                                Reserva como administrador: sin limites semanales o diarios de usuario, manteniendo el control de plazas disponibles.
                            <?php else: ?>
                                Reglas activas: maximo 2 reservas por dia, 1 viaje del mismo tipo por semana y 4 plazas semanales en total. La cuota online es 1,00 € y el resto del viaje se paga fuera de la plataforma.
                            <?php endif; ?>
                        </div>
                        <div class="notice error trip-booking-feedback" hidden></div>
                    <?php else: ?>
                        <div class="notice error">
                            <?= $hasPriceDefined ? 'Este viaje ya no admite reservas.' : 'Este viaje aun no tiene un precio por plaza definido y no puede reservarse.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
