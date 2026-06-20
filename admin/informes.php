<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$summary = admin_reservation_report_summary();
$result = admin_past_trip_reports(['search' => $search, 'status' => $status, 'page' => $page]);
$sidebarLinks = admin_sidebar_links('reports');

$pageTitle = 'Informes';

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
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/informes-pdf.php') ?>">Exportar PDF</a>
        </div>
    </aside>

    <div class="admin-dashboard">
    <section class="admin-hero-card card">
        <div class="card-body admin-hero-card-body">
            <div>
                <span class="admin-hero-badge">Historical insights</span>
                <h1>Informes de reservas</h1>
                <p class="muted">Resumen de reservas pasadas, viajeros transportados y viajes completados con exito.</p>
            </div>
            <div class="admin-hero-meta">
                <div class="admin-hero-meta-card">
                    <span>Modulo</span>
                    <strong>Informes</strong>
                </div>
                <div class="admin-hero-meta-card">
                    <span>Accion</span>
                    <strong><?= (int) $result['total'] ?> viajes analizados</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-stats-grid admin-stats-grid--reports">
        <article class="card admin-stat-card">
            <div class="card-body">
                <strong><?= (int) $summary['past_reservations_total'] ?></strong>
                <span>Reservas pasadas</span>
            </div>
        </article>
        <article class="card admin-stat-card">
            <div class="card-body">
                <strong><?= (int) $summary['travelled_passengers_total'] ?></strong>
                <span>Viajeros transportados</span>
            </div>
        </article>
        <article class="card admin-stat-card">
            <div class="card-body">
                <strong><?= (int) $summary['successful_trips_total'] ?></strong>
                <span>Viajes completados</span>
            </div>
        </article>
        <article class="card admin-stat-card">
            <div class="card-body">
                <strong><?= (int) $summary['success_rate'] ?>%</strong>
                <span>Tasa de exito</span>
            </div>
        </article>
        <article class="card admin-stat-card admin-stat-card--revenue">
            <div class="card-body">
                <strong><?= e(format_money((float) ($summary['collected_revenue_total'] ?? 0))) ?></strong>
                <span>Cuotas cobradas</span>
            </div>
        </article>
    </section>

    <section class="card admin-panel-card">
        <div class="card-body">
            <form class="admin-filter-bar" method="get">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar por viaje, ruta o conductor">
                <select name="status" aria-label="Filtrar por estado del viaje">
                    <option value=""<?= $status === '' ? ' selected' : '' ?>>Todos</option>
                    <option value="completed"<?= $status === 'completed' ? ' selected' : '' ?>>Completados</option>
                    <option value="not_completed"<?= $status === 'not_completed' ? ' selected' : '' ?>>No completados</option>
                </select>
                <button class="button button-primary" type="submit">Filtrar</button>
                <a class="button button-ghost" href="<?= app_url('/admin/informes.php') ?>">Limpiar</a>
            </form>
            <p class="muted admin-results-copy">
                <?= (int) $result['total'] ?> viajes pasados analizados<?= ($search !== '' || $status !== '') ? ' con los filtros aplicados' : '' ?>.
            </p>

            <?php if (!$result['items']): ?>
                <article class="card empty-state">
                    <div class="card-body">No hay viajes pasados para mostrar en el informe.</div>
                </article>
            <?php else: ?>
                <div class="admin-list">
                    <?php foreach ($result['items'] as $item): ?>
                        <?php
                        $travelledPassengers = (int) $item['travelled_passengers'];
                        $cancelledReservations = (int) $item['cancelled_reservations'];
                        $completedReservations = (int) $item['completed_reservations'];
                        $reportLabel = $travelledPassengers > 0 ? 'Completado con exito' : ($cancelledReservations > 0 ? 'Sin viaje efectivo' : 'Sin reservas');
                        $reportClass = $travelledPassengers > 0 ? 'status-published' : 'status-cancelled';
                        ?>
                        <article class="admin-row">
                            <div class="admin-row-main">
                                <strong><?= e((string) $item['name']) ?></strong>
                                <span class="muted"><?= e((string) $item['origin']) ?> → <?= e((string) $item['destination']) ?> · <?= e(format_datetime((string) $item['departure_at'])) ?><?= !empty($item['creator_name']) ? ' · ' . e((string) $item['creator_name']) : '' ?></span>
                                <div class="meta-row">
                                    <span class="pill"><?= $travelledPassengers ?> viajeros</span>
                                    <span class="pill"><?= $completedReservations ?> reservas completadas</span>
                                    <span class="pill"><?= $cancelledReservations ?> canceladas</span>
                                    <span class="pill price-pill"><?= e(reservation_total_label($item['collected_amount'] ?? 0)) ?></span>
                                    <span class="pill <?= $reportClass ?>"><?= $reportLabel ?></span>
                                </div>
                            </div>
                            <div class="admin-row-actions">
                                <a class="button button-primary" href="<?= app_url('/admin/informe-viaje.php?id=' . (int) $item['id']) ?>">Ver viaje</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($result['pages'] > 1): ?>
                    <nav class="admin-pagination" aria-label="Paginacion">
                        <?php for ($i = 1; $i <= (int) $result['pages']; $i++): ?>
                            <a href="<?= e(app_url('/admin/informes.php?search=' . urlencode($search) . '&status=' . urlencode($status) . '&page=' . $i)) ?>"<?= $i === (int) $result['page'] ? ' aria-current="page"' : '' ?>><?= $i ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
