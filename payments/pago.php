<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$reservationId = (int) ($_GET['reservation_id'] ?? 0);
$reservation = $reservationId > 0 ? find_user_reservation($reservationId, (int) current_user()['id']) : null;

if (!$reservation) {
    set_flash('error', 'No hemos encontrado esa reserva para revisar el pago.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$pageTitle = 'Pago de reserva';
$paymentCanStart = reservation_can_start_payment($reservation);
$stripeReady = stripe_is_configured();

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Pago de la reserva</h1>
        <p class="muted">La app solo cobra 1,00 € por formalizar la reserva. El resto se acuerda directamente entre pasajero y conductor.</p>
    </div>
    <a class="button button-ghost" href="<?= app_url('/trips/mis-reservas.php') ?>">Volver a mis reservas</a>
</section>

<section class="payment-shell">
    <article class="card payment-summary-card">
        <div class="card-body">
            <div class="payment-summary-head">
                <div>
                    <span class="detail-eyebrow">Reserva #<?= (int) $reservation['id'] ?></span>
                    <h2><?= e((string) $reservation['trip_name']) ?></h2>
                    <p class="muted"><?= e((string) $reservation['origin']) ?> → <?= e((string) $reservation['destination']) ?></p>
                </div>
                <span class="pill <?= e(payment_status_badge_class((string) ($reservation['payment_status'] ?? 'pending'))) ?>">
                    <?= e(payment_status_label((string) ($reservation['payment_status'] ?? 'pending'))) ?>
                </span>
            </div>

            <div class="payment-summary-grid">
                <div class="payment-summary-item">
                    <span>Fecha</span>
                    <strong><?= e(format_datetime((string) $reservation['departure_at'])) ?></strong>
                </div>
                <div class="payment-summary-item">
                    <span>Plazas</span>
                    <strong><?= (int) $reservation['seats_reserved'] ?></strong>
                </div>
                <div class="payment-summary-item">
                    <span>Precio por plaza</span>
                    <strong><?= e(format_money((float) ($reservation['price_per_seat'] ?? 0))) ?></strong>
                </div>
                <div class="payment-summary-item payment-summary-total">
                    <span>Pago al conductor estimado</span>
                    <strong><?= e(format_money(reservation_driver_estimate($reservation['price_per_seat'] ?? 0, (int) ($reservation['seats_reserved'] ?? 1)))) ?></strong>
                </div>
                <div class="payment-summary-item payment-summary-total">
                    <span>Cuota app</span>
                    <strong><?= e(format_money((float) ($reservation['total_price'] ?? 0))) ?></strong>
                </div>
            </div>

            <div class="notice success">
                El precio del conductor es orientativo para el viaje. El pago online de esta plataforma solo cubre la reserva. El resto se paga fuera de la app por <?= e(off_platform_payment_methods_label()) ?>.
            </div>

            <?php if ((string) ($reservation['payment_status'] ?? 'pending') === 'paid'): ?>
                <div class="notice success">El pago de esta reserva ya esta confirmado.</div>
            <?php elseif (!$paymentCanStart): ?>
                <div class="notice error">Esta reserva ya no puede iniciar un pago online porque ha cambiado de estado o el viaje ya no es futuro.</div>
            <?php elseif ($stripeReady): ?>
                <div class="payment-actions">
                    <form method="post" action="<?= app_url('/actions/create_checkout_session.php') ?>">
                        <input type="hidden" name="reservation_id" value="<?= (int) $reservation['id'] ?>">
                        <button class="button button-primary" type="submit">Pagar 1,00 € con Stripe</button>
                    </form>
                    <p class="muted">Seras redirigido a Stripe para pagar unicamente la cuota de reserva.</p>
                </div>
            <?php else: ?>
                <div class="notice warning">
                    Stripe todavia no esta configurado en este entorno. La reserva queda creada y el resto del importe se abonaria al conductor por <?= e(off_platform_payment_methods_label()) ?>.
                </div>
            <?php endif; ?>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
