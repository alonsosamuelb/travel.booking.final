<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Informe de reservas';
$reservations = my_reservations((int) current_user()['id']);

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="report-shell">
    <div class="section-head">
        <div>
            <h1>Informe de reservas</h1>
            <p class="muted">Vista preparada para imprimir o guardar como PDF desde el navegador.</p>
        </div>
        <div class="toolbar">
            <a class="button button-primary" href="<?= app_url('/reports/informe-reservas-pdf.php') ?>">Descargar PDF</a>
            <button class="button button-primary" type="button" onclick="window.print()">Imprimir / PDF</button>
            <a class="button button-ghost" href="<?= app_url('/trips/mis-reservas.php') ?>">Volver</a>
        </div>
    </div>

    <section class="card admin-panel-card">
        <div class="card-body">
            <?php if (!$reservations): ?>
                <p class="muted">No hay reservas registradas.</p>
            <?php else: ?>
                <div class="report-list">
                    <?php foreach ($reservations as $reservation): ?>
                        <article class="report-row">
                            <strong><?= e((string) $reservation['trip_name']) ?></strong>
                            <span><?= e((string) $reservation['origin']) ?> → <?= e((string) $reservation['destination']) ?></span>
                            <span><?= e(format_datetime((string) $reservation['departure_at'])) ?></span>
                            <span><?= (int) $reservation['seats_reserved'] ?> plazas</span>
                            <span><?= e(reservation_driver_estimate_label($reservation['price_per_seat'] ?? 0, (int) $reservation['seats_reserved'])) ?></span>
                            <span><?= e(reservation_total_label($reservation['total_price'] ?? 0)) ?></span>
                            <span><?= e(payment_status_label((string) ($reservation['payment_status'] ?? 'pending'))) ?></span>
                            <span><?= e(reservation_status_label((string) $reservation['status'])) ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
