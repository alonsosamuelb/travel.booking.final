<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$search = trim((string) ($_GET['search'] ?? ''));
$paymentStatus = trim((string) ($_GET['payment_status'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = [
    'search' => $search,
    'payment_status' => $paymentStatus,
    'page' => $page,
];

$summary = admin_commission_summary($filters);
$result = admin_commissions($filters);
$sidebarLinks = admin_sidebar_links('commissions');

$pageTitle = 'Gestion de comisiones';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="admin-executive-shell">
    <aside class="admin-sidebar card">
        <div class="admin-sidebar-top">
            <span class="admin-sidebar-eyebrow">Control center</span>
            <h2>Travel Admin</h2>
            <p class="muted">Gestion ejecutiva de usuarios, viajes, reservas, comisiones e informes.</p>
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
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/admin.php') ?>">Volver al panel</a>
        </div>
    </aside>

    <div class="admin-dashboard">
        <section class="admin-hero-card card">
            <div class="card-body admin-hero-card-body">
                <div>
                    <span class="admin-hero-badge">App revenue</span>
                    <h1>Gestion de comisiones</h1>
                    <p class="muted">Seguimiento de la cuota de reserva de la app con detalle de pasajero, conductor, fechas de pago y estado de cobro.</p>
                </div>
                <div class="admin-hero-meta">
                    <div class="admin-hero-meta-card">
                        <span>Modulo</span>
                        <strong>Comisiones</strong>
                    </div>
                    <div class="admin-hero-meta-card">
                        <span>Registros</span>
                        <strong><?= (int) ($result['total'] ?? 0) ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-stats-grid admin-stats-grid--reports">
            <article class="card admin-stat-card">
                <div class="card-body">
                    <strong><?= (int) ($summary['commissions_total'] ?? 0) ?></strong>
                    <span>Cuotas registradas</span>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="card-body">
                    <strong><?= (int) ($summary['paid_count'] ?? 0) ?></strong>
                    <span>Pagadas</span>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="card-body">
                    <strong><?= (int) ($summary['pending_count'] ?? 0) ?></strong>
                    <span>Pendientes</span>
                </div>
            </article>
            <article class="card admin-stat-card">
                <div class="card-body">
                    <strong><?= (int) ($summary['refunded_count'] ?? 0) ?></strong>
                    <span>Reembolsadas</span>
                </div>
            </article>
            <article class="card admin-stat-card admin-stat-card--revenue">
                <div class="card-body">
                    <strong><?= e(format_money((float) ($summary['paid_amount_total'] ?? 0))) ?></strong>
                    <span>Total cobrado</span>
                </div>
            </article>
        </section>

        <section class="card admin-panel-card">
            <div class="card-body">
                <form class="admin-filter-bar" method="get">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar por pasajero, conductor o viaje">
                    <select name="payment_status">
                        <option value="">Todos los estados</option>
                        <option value="paid"<?= $paymentStatus === 'paid' ? ' selected' : '' ?>>Pagadas</option>
                        <option value="pending"<?= $paymentStatus === 'pending' ? ' selected' : '' ?>>Pendientes</option>
                        <option value="refunded"<?= $paymentStatus === 'refunded' ? ' selected' : '' ?>>Reembolsadas</option>
                    </select>
                    <button class="button button-primary" type="submit">Filtrar</button>
                    <a class="button button-ghost" href="<?= app_url('/admin/comisiones.php') ?>">Limpiar</a>
                </form>

                <div class="admin-commission-summary">
                    <span class="pill"><?= e(format_money((float) ($summary['pending_amount_total'] ?? 0))) ?> pendientes</span>
                    <span class="pill"><?= e(format_money((float) ($summary['refunded_amount_total'] ?? 0))) ?> reembolsadas</span>
                    <span class="pill">
                        <?= !empty($summary['last_paid_at']) ? 'Ultimo pago: ' . e(format_datetime((string) $summary['last_paid_at'])) : 'Sin pagos confirmados' ?>
                    </span>
                </div>

                <p class="muted admin-results-copy"><?= (int) ($result['total'] ?? 0) ?> cuotas encontradas<?= $search !== '' || $paymentStatus !== '' ? ' con los filtros aplicados' : '' ?>.</p>

                <?php if (empty($result['items'])): ?>
                    <article class="card empty-state">
                        <div class="card-body">No hay comisiones para mostrar con esos criterios.</div>
                    </article>
                <?php else: ?>
                    <div class="admin-list">
                        <?php foreach ($result['items'] as $item): ?>
                            <article class="admin-row">
                                <div class="admin-row-main">
                                    <strong><?= e((string) $item['trip_name']) ?></strong>
                                    <span class="muted">
                                        Pasajero: <?= e((string) $item['passenger_name']) ?> ·
                                        Conductor: <?= !empty($item['driver_name']) ? e((string) $item['driver_name']) : 'No asignado' ?>
                                    </span>
                                    <div class="meta-row">
                                        <span class="pill"><?= e((string) $item['origin']) ?> → <?= e((string) $item['destination']) ?></span>
                                        <span class="pill"><?= e(reservation_total_label($item['total_price'] ?? 0)) ?></span>
                                        <span class="pill"><?= e(payment_status_label((string) ($item['payment_status'] ?? 'pending'))) ?></span>
                                        <span class="pill"><?= !empty($item['paid_at']) ? 'Pagado: ' . e(format_datetime((string) $item['paid_at'])) : 'Creada: ' . e(format_datetime((string) $item['created_at'])) ?></span>
                                    </div>
                                    <div class="meta-row">
                                        <span class="pill">Reserva #<?= (int) $item['id'] ?></span>
                                        <span class="pill">Pasajero: <?= e((string) $item['passenger_email']) ?></span>
                                        <?php if (!empty($item['driver_email'])): ?>
                                            <span class="pill">Conductor: <?= e((string) $item['driver_email']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['stripe_payment_intent_id'])): ?>
                                            <span class="pill">PI: <?= e((string) $item['stripe_payment_intent_id']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="admin-row-actions">
                                    <a class="button button-primary" href="<?= app_url('/admin/reserva-form.php?id=' . (int) $item['id']) ?>">Ver reserva</a>
                                    <a class="button button-ghost" href="<?= app_url('/admin/informe-viaje.php?id=' . (int) $item['trip_id']) ?>">Ver viaje</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ((int) ($result['pages'] ?? 1) > 1): ?>
                        <nav class="admin-pagination" aria-label="Paginacion">
                            <?php for ($i = 1; $i <= (int) $result['pages']; $i++): ?>
                                <a href="<?= e(app_url('/admin/comisiones.php?search=' . urlencode($search) . '&payment_status=' . urlencode($paymentStatus) . '&page=' . $i)) ?>"<?= $i === (int) $result['page'] ? ' aria-current="page"' : '' ?>><?= $i ?></a>
                            <?php endfor; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
