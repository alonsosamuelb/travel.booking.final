<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$reservationId = (int) ($_GET['reservation_id'] ?? 0);
$reservation = $reservationId > 0 ? find_user_reservation($reservationId, (int) current_user()['id']) : null;
$pageTitle = 'Pago cancelado';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Pago no completado</h1>
        <p class="muted">La reserva sigue creada, pero la cuota online de 1,00 € no se ha finalizado todavia.</p>
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
            <p class="muted">Si completas la cuota de reserva, el pago restante se hace directamente con el conductor por <?= e(off_platform_payment_methods_label()) ?>.</p>
        <?php endif; ?>

        <div class="toolbar" style="margin-top: 20px;">
            <a class="button button-primary" href="<?= app_url('/trips/mis-reservas.php') ?>">Volver a mis reservas</a>
            <a class="button button-ghost" href="<?= app_url('/trips/viajes.php') ?>">Volver a viajes</a>
        </div>
    </div>
</article>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
