<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Panel admin';
$kpis = admin_dashboard_kpis();
$monthlyReservations = admin_dashboard_monthly_reservations();
$statusBreakdown = admin_dashboard_status_breakdown();
$sidebarLinks = admin_sidebar_links('dashboard');
$quickActions = [
    ['label' => 'Crear viaje', 'href' => app_url('/admin/viaje-form.php'), 'tone' => 'blue'],
    ['label' => 'Crear usuario', 'href' => app_url('/admin/usuario-form.php'), 'tone' => 'teal'],
    ['label' => 'Nueva reserva', 'href' => app_url('/admin/reserva-form.php'), 'tone' => 'amber'],
    ['label' => 'Ver comisiones', 'href' => app_url('/admin/comisiones.php'), 'tone' => 'green'],
];

$lineValues = array_map(static fn (array $point): int => (int) $point['value'], $monthlyReservations);
$maxLineValue = max(1, ...$lineValues);
$linePoints = [];
$lineCount = count($monthlyReservations);

foreach ($monthlyReservations as $index => $point) {
    $x = $lineCount > 1 ? (int) round(($index / ($lineCount - 1)) * 100) : 50;
    $y = 100 - (int) round(((int) $point['value'] / $maxLineValue) * 88);
    $linePoints[] = $x . ',' . $y;
}

$linePolyline = implode(' ', $linePoints);
$statusTotal = max(1, array_sum($statusBreakdown));
$statusSegments = [
    ['label' => 'Activas', 'value' => (int) $statusBreakdown['active'], 'color' => '#2ea6df'],
    ['label' => 'Completadas', 'value' => (int) $statusBreakdown['completed'], 'color' => '#1dbf73'],
    ['label' => 'Canceladas', 'value' => (int) $statusBreakdown['cancelled'], 'color' => '#ef5b5b'],
];

$offset = 0.0;
$donutParts = [];
foreach ($statusSegments as $segment) {
    $portion = (($segment['value'] / $statusTotal) * 100);
    $donutParts[] = $segment['color'] . ' ' . round($offset, 2) . '% ' . round($offset + $portion, 2) . '%';
    $offset += $portion;
}
$donutGradient = 'conic-gradient(' . implode(', ', $donutParts) . ')';

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
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/informes.php') ?>">Abrir informes</a>
        </div>
    </aside>

    <div class="admin-dashboard">
        <section class="admin-hero-card card">
            <div class="card-body admin-hero-card-body">
                <div>
                    <span class="admin-hero-badge">Executive dashboard</span>
                    <h1>Panel de Control</h1>
                    <p class="muted">Vista ejecutiva del sistema con reservas, demanda, operaciones recientes y alertas accionables en tiempo real.</p>
                </div>
                <div class="admin-hero-meta">
                    <div class="admin-hero-meta-card">
                        <span>Sesion activa</span>
                        <strong><?= e((string) current_user()['full_name']) ?></strong>
                    </div>
                    <div class="admin-hero-meta-card">
                        <span>Estado</span>
                        <strong>Operativo</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-kpi-grid">
            <?php foreach ($kpis as $kpi): ?>
                <?php $trendPositive = (int) $kpi['trend'] >= 0; ?>
                <article class="card admin-kpi-card tone-<?= e((string) $kpi['tone']) ?>">
                    <div class="card-body">
                        <div class="admin-kpi-top">
                            <span class="admin-kpi-icon"><?= e(strtoupper(substr((string) $kpi['icon'], 0, 1))) ?></span>
                            <span class="admin-kpi-trend <?= $trendPositive ? 'is-up' : 'is-down' ?>">
                                <?= $trendPositive ? '+' : '' ?><?= (int) $kpi['trend'] ?>%
                            </span>
                        </div>
                        <div class="admin-kpi-value"><?= e((string) $kpi['value']) ?></div>
                        <h2><?= e((string) $kpi['label']) ?></h2>
                        <p class="muted"><?= e((string) $kpi['context']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-analytics-grid">
            <article class="card admin-chart-card admin-line-card">
                <div class="card-body">
                    <div class="admin-card-head">
                        <div>
                            <span class="admin-card-kicker">Analitica</span>
                            <h2>Reservas por mes</h2>
                        </div>
                        <span class="pill"><?= count($monthlyReservations) ?> meses</span>
                    </div>
                    <div class="admin-line-chart">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                            <polyline class="admin-line-area" points="0,100 <?= e($linePolyline) ?> 100,100"></polyline>
                            <polyline class="admin-line-path" points="<?= e($linePolyline) ?>"></polyline>
                            <?php foreach ($monthlyReservations as $index => $point): ?>
                                <?php
                                $x = $lineCount > 1 ? (int) round(($index / ($lineCount - 1)) * 100) : 50;
                                $y = 100 - (int) round(((int) $point['value'] / $maxLineValue) * 88);
                                ?>
                                <circle cx="<?= $x ?>" cy="<?= $y ?>" r="2.4" class="admin-line-dot"></circle>
                            <?php endforeach; ?>
                        </svg>
                    </div>
                    <div class="admin-line-labels">
                        <?php foreach ($monthlyReservations as $point): ?>
                            <div>
                                <strong><?= (int) $point['value'] ?></strong>
                                <span><?= e((string) $point['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>

            <article class="card admin-chart-card admin-donut-card">
                <div class="card-body">
                    <div class="admin-card-head">
                        <div>
                            <span class="admin-card-kicker">Estado global</span>
                            <h2>Estado de reservas</h2>
                        </div>
                        <span class="pill"><?= $statusTotal ?> total</span>
                    </div>
                    <div class="admin-donut-wrap">
                        <div class="admin-donut-chart" style="background: <?= e($donutGradient) ?>">
                            <div class="admin-donut-hole">
                                <strong><?= $statusTotal ?></strong>
                                <span>reservas</span>
                            </div>
                        </div>
                        <div class="admin-donut-legend">
                            <?php foreach ($statusSegments as $segment): ?>
                                <div class="admin-donut-legend-item">
                                    <span class="admin-donut-color" style="background: <?= e($segment['color']) ?>"></span>
                                    <div>
                                        <strong><?= (int) $segment['value'] ?></strong>
                                        <span><?= e($segment['label']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <section class="admin-data-grid">
            <article class="card admin-side-stack admin-side-stack-full">
                <div class="card-body">
                    <div class="admin-card-head">
                        <div>
                            <span class="admin-card-kicker">Accion inmediata</span>
                            <h2>Acciones rapidas</h2>
                        </div>
                    </div>
                    <div class="admin-quick-actions">
                        <?php foreach ($quickActions as $action): ?>
                            <a class="admin-quick-action tone-<?= e($action['tone']) ?>" href="<?= e($action['href']) ?>">
                                <span><?= e($action['label']) ?></span>
                                <strong>Ir</strong>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>
        </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
