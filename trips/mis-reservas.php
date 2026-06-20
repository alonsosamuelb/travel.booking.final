<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Mis reservas';
$reservations = my_reservations((int) current_user()['id']);
$activeReservations = array_values(array_filter($reservations, static fn (array $reservation): bool => $reservation['status'] === 'active'));
$completedReservations = array_values(array_filter($reservations, static fn (array $reservation): bool => $reservation['status'] === 'completed'));
$cancelledReservations = array_values(array_filter($reservations, static fn (array $reservation): bool => $reservation['status'] === 'cancelled'));

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Mis reservas</h1>
        <p class="muted">Consulta tus reservas activas y revisa con mas claridad tu historial de viajes.</p>
    </div>
    <a class="button button-ghost" href="<?= app_url('/reports/informe-reservas.php') ?>">Informe imprimible</a>
</section>

<?php if (!$reservations): ?>
    <article class="card empty-state">
        <div class="card-body">Todavia no tienes reservas registradas.</div>
    </article>
<?php else: ?>
    <?php
    $groups = [
        'Activas' => $activeReservations,
        'Completadas' => $completedReservations,
        'Canceladas' => $cancelledReservations,
    ];
    ?>

    <?php foreach ($groups as $label => $group): ?>
        <?php if (!$group): ?>
            <?php continue; ?>
        <?php endif; ?>
        <section class="reservation-history-group">
            <div class="section-head">
                <div>
                    <h2><?= e($label) ?></h2>
                    <p class="muted"><?= count($group) ?> reservas en esta seccion.</p>
                </div>
            </div>
            <section class="cards-grid">
                <?php foreach ($group as $reservation): ?>
                    <article class="card trip-card">
                        <a class="trip-card-media-link" href="<?= e(app_url('/trips/viaje.php?id=' . (int) $reservation['trip_id'])) ?>">
                            <img class="trip-cover" src="<?= e(destination_image($reservation['destination'] ?? null, $reservation['image_path'] ?? null)) ?>" alt="<?= e($reservation['trip_name']) ?>">
                        </a>
                        <div class="card-body">
                            <h3><a class="trip-card-title-link" href="<?= e(app_url('/trips/viaje.php?id=' . (int) $reservation['trip_id'])) ?>"><?= e($reservation['trip_name']) ?></a></h3>
                            <p class="muted"><?= e($reservation['origin']) ?> → <?= e($reservation['destination']) ?></p>
                            <div class="meta-row">
                                <span class="pill"><?= e(format_datetime($reservation['departure_at'])) ?></span>
                                <span class="pill"><?= (int) $reservation['seats_reserved'] ?> plazas</span>
                                <span class="pill"><?= e(trip_price_label($reservation['price_per_seat'] ?? 0)) ?></span>
                                <span class="pill"><?= e(reservation_driver_estimate_label($reservation['price_per_seat'] ?? 0, (int) $reservation['seats_reserved'])) ?></span>
                                <span class="pill"><?= e(reservation_total_label($reservation['total_price'] ?? 0)) ?></span>
                                <span class="pill <?= e(payment_status_badge_class((string) ($reservation['payment_status'] ?? 'pending'))) ?>"><?= e(payment_status_label((string) ($reservation['payment_status'] ?? 'pending'))) ?></span>
                                <span class="pill"><?= e($reservation['vehicle']) ?></span>
                                <span class="pill status-<?= e($reservation['status']) ?>"><?= e(reservation_status_label($reservation['status'])) ?></span>
                            </div>
                            <p class="muted">La cuota online de la app es 1,00 €. El resto se paga al conductor por <?= e(off_platform_payment_methods_label()) ?>.</p>
                            <?php if (!empty($reservation['notes'])): ?>
                                <p class="muted"><?= e($reservation['notes']) ?></p>
                            <?php endif; ?>
                            <?php if ($reservation['status'] === 'active'): ?>
                                <div class="toolbar">
                                    <a class="button button-ghost" href="<?= app_url('/trips/editar-reserva.php?id=' . (int) $reservation['id']) ?>">Editar reserva</a>
                                    <?php if ((string) ($reservation['payment_status'] ?? 'pending') === 'pending'): ?>
                                        <a class="button button-primary" href="<?= app_url('/payments/pago.php?reservation_id=' . (int) $reservation['id']) ?>">
                                            <?= stripe_is_configured() && reservation_can_start_payment($reservation) ? 'Completar pago' : 'Revisar pago' ?>
                                        </a>
                                    <?php elseif ((string) ($reservation['payment_status'] ?? '') === 'paid'): ?>
                                        <span class="pill status-published">Pago confirmado</span>
                                    <?php endif; ?>
                                    <form method="post" action="<?= app_url('/actions/cancel_reservation.php') ?>" onsubmit="return confirm('Se cancelara esta reserva.');">
                                        <input type="hidden" name="reservation_id" value="<?= (int) $reservation['id'] ?>">
                                        <button class="button button-danger" type="submit">Cancelar reserva</button>
                                    </form>
                                </div>
                            <?php elseif ($reservation['status'] === 'completed'): ?>
                                <?php
                                $reviewContext = review_context_for_user((int) $reservation['id'], (int) current_user()['id']);
                                $existingReview = ($reviewContext && (string) $reviewContext['mode'] === 'passenger_to_driver')
                                    ? user_review_for_pair((int) $reservation['id'], (int) current_user()['id'], (int) $reviewContext['reviewee_id'])
                                    : null;
                                ?>
                                <?php if ($reviewContext && (string) $reviewContext['mode'] === 'passenger_to_driver'): ?>
                                    <a class="button button-ghost" href="<?= app_url('/trips/valorar-reserva.php?reservation_id=' . (int) $reservation['id']) ?>">
                                        <?= $existingReview ? 'Editar valoracion del conductor' : 'Valorar conductor' ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
