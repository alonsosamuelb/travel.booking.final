<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$sessionId = trim((string) ($_GET['session_id'] ?? ''));
$reservationId = (int) ($_GET['reservation_id'] ?? 0);
$reservation = $reservationId > 0 ? find_user_reservation($reservationId, (int) current_user()['id']) : null;
$pageTitle = 'Pago completado';

if ($sessionId !== '' && stripe_is_configured()) {
    try {
        $session = stripe_retrieve_checkout_session($sessionId);
        sync_reservation_payment_from_checkout_session($session);
        $reservation = $reservationId > 0 ? find_user_reservation($reservationId, (int) current_user()['id']) : $reservation;
    } catch (Throwable $exception) {
        set_flash('error', 'El pago se ha iniciado, pero no pudimos confirmar el estado automaticamente.');
    }
}

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Pago registrado</h1>
        <p class="muted">La cuota online de 1,00 € ha pasado por el flujo de cobro. El resto del viaje se paga fuera de la plataforma.</p>
    </div>
</section>

<article class="card">
    <div class="card-body">
        <?php if ($reservation): ?>
            <h2><?= e((string) $reservation['trip_name']) ?></h2>
            <p class="muted"><?= e((string) $reservation['origin']) ?> → <?= e((string) $reservation['destination']) ?></p>
            <div class="meta-row">
                <span class="pill"><?= reservation_total_label($reservation['total_price'] ?? 0) ?></span>
                <span class="pill"><?= reservation_driver_estimate_label($reservation['price_per_seat'] ?? 0, (int) ($reservation['seats_reserved'] ?? 1)) ?></span>
                <span class="pill <?= e(payment_status_badge_class((string) ($reservation['payment_status'] ?? 'pending'))) ?>">
                    <?= e(payment_status_label((string) ($reservation['payment_status'] ?? 'pending'))) ?>
                </span>
            </div>
            <p class="muted">Recuerda que el importe restante se abona directamente al conductor por <?= e(off_platform_payment_methods_label()) ?>.</p>
        <?php else: ?>
            <p class="muted">No hemos podido cargar el detalle de la reserva, pero el flujo de pago ya ha terminado.</p>
        <?php endif; ?>

        <div class="toolbar" style="margin-top: 20px;">
            <a class="button button-primary" href="<?= app_url('/trips/mis-reservas.php') ?>">Ir a mis reservas</a>
            <a class="button button-ghost" href="<?= app_url('/trips/viajes.php') ?>">Seguir explorando</a>
        </div>
    </div>
</article>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
