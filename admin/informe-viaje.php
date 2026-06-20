<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$tripId = max(0, (int) ($_GET['id'] ?? 0));
$trip = $tripId > 0 ? admin_past_trip_report_detail($tripId) : null;

if (!$trip) {
    set_flash('error', 'No se encontro un viaje historico para consultar.');
    redirect_to(app_url('/admin/informes.php'));
}

$travelledPassengers = (int) $trip['travelled_passengers'];
$completedReservations = (int) $trip['completed_reservations'];
$cancelledReservations = (int) $trip['cancelled_reservations'];
$totalReservations = (int) $trip['total_reservations'];
$reportLabel = $travelledPassengers > 0 ? 'Viaje completado con exito' : ($cancelledReservations > 0 ? 'Viaje sin salida efectiva' : 'Viaje sin reservas');
$sidebarLinks = admin_sidebar_links('reports');

$pageTitle = 'Informe del viaje';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="admin-executive-shell">
    <aside class="admin-sidebar card">
        <div class="admin-sidebar-top">
            <span class="admin-sidebar-eyebrow">Control center</span>
            <h2>Travel Admin</h2>
            <p class="muted">Gestion ejecutiva de usuarios, viajes, reservas e informes.</p>
        </div>
        <nav class="admin-sidebar-nav" aria-label="Navegacion del panel">
            <?php foreach ($sidebarLinks as $sidebarLink): ?>
                <a class="admin-sidebar-link<?= $sidebarLink['active'] ? ' is-active' : '' ?>" href="<?= e($sidebarLink['href']) ?>">
                    <span class="admin-sidebar-link-dot"></span>
                    <span><?= e($sidebarLink['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-foot">
            <div class="admin-sidebar-profile">
                <strong><?= e((string) current_user()['full_name']) ?></strong>
                <span>Administrador</span>
            </div>
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/informes.php') ?>">Volver a informes</a>
        </div>
    </aside>

    <div class="admin-dashboard">
    <section class="trip-detail-shell">
    <div class="trip-detail-back">
        <a href="<?= app_url('/admin/informes.php') ?>">← Volver a informes</a>
    </div>

    <div class="trip-detail-grid">
        <article class="card trip-detail-media">
            <img class="trip-detail-cover" src="<?= e(destination_image($trip['destination'] ?? null, $trip['image_path'] ?? null)) ?>" alt="<?= e((string) $trip['name']) ?>">
        </article>

        <article class="card trip-detail-main">
            <div class="card-body">
                <span class="detail-eyebrow">Informe historico</span>
                <h1><?= e((string) $trip['name']) ?></h1>
                <p class="trip-detail-route"><?= e((string) $trip['origin']) ?> → <?= e((string) $trip['destination']) ?></p>

                <div class="meta-row trip-detail-meta">
                    <span class="pill"><?= e(format_datetime((string) $trip['departure_at'])) ?></span>
                    <span class="pill"><?= e((string) $trip['vehicle']) ?></span>
                    <span class="pill status-published"><?= e($reportLabel) ?></span>
                </div>

                <div class="trip-detail-copy">
                    <p><?= nl2br(e((string) ($trip['description'] ?: 'Sin descripcion adicional.'))) ?></p>
                </div>

                <div class="trip-driver-card">
                    <h2>Resumen del viaje concluido</h2>
                    <div class="trip-driver-grid">
                        <div>
                            <span class="trip-driver-label">Conductor</span>
                            <strong><?= e((string) ($trip['creator_name'] ?: 'No especificado')) ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Correo</span>
                            <strong><?= e((string) ($trip['creator_email'] ?: 'No disponible')) ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Viajeros transportados</span>
                            <strong><?= $travelledPassengers ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Reservas completadas</span>
                            <strong><?= $completedReservations ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Reservas canceladas</span>
                            <strong><?= $cancelledReservations ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Reservas totales</span>
                            <strong><?= $totalReservations ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Precio por plaza</span>
                            <strong><?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?></strong>
                        </div>
                        <div>
                            <span class="trip-driver-label">Cuotas app cobradas</span>
                            <strong><?= e(reservation_total_label($trip['collected_amount'] ?? 0)) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="trip-detail-actions">
                    <div class="notice success">
                        Esta ficha es solo de consulta historica. Desde el informe no se puede editar un viaje ya concluido.
                    </div>
                </div>
            </div>
        </article>
    </div>

    <section class="card admin-panel-card">
        <div class="card-body">
            <div class="section-head">
                <div>
                    <h2>Reservas asociadas</h2>
                    <p class="muted">Detalle de quien reservo, cuantas plazas y como termino cada solicitud.</p>
                </div>
            </div>

            <?php if (empty($trip['reservations'])): ?>
                <article class="card empty-state">
                    <div class="card-body">Este viaje no tuvo reservas registradas.</div>
                </article>
            <?php else: ?>
                <div class="admin-list">
                    <?php foreach ($trip['reservations'] as $reservation): ?>
                        <article class="admin-row">
                            <div class="admin-row-main">
                                <strong><?= e((string) $reservation['full_name']) ?></strong>
                                <span class="muted"><?= e((string) $reservation['email']) ?> · <?= e(format_datetime((string) $reservation['created_at'])) ?></span>
                                <div class="meta-row">
                                    <span class="pill"><?= (int) $reservation['seats_reserved'] ?> plazas</span>
                                    <span class="pill"><?= e(trip_price_label($reservation['price_per_seat'] ?? 0)) ?></span>
                                    <span class="pill"><?= e(reservation_driver_estimate_label($reservation['price_per_seat'] ?? 0, (int) $reservation['seats_reserved'])) ?></span>
                                    <span class="pill"><?= e(reservation_total_label($reservation['total_price'] ?? 0)) ?></span>
                                    <span class="pill"><?= e(payment_status_label((string) ($reservation['payment_status'] ?? 'pending'))) ?></span>
                                    <span class="pill"><?= e((string) $reservation['travel_role']) ?></span>
                                    <span class="pill status-<?= e((string) $reservation['status']) ?>"><?= e(reservation_status_label((string) $reservation['status'])) ?></span>
                                </div>
                                <?php if (!empty($reservation['notes'])): ?>
                                    <p class="muted"><?= e((string) $reservation['notes']) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
