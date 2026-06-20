<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$search = trim((string) ($_GET['search'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = admin_trips(['search' => $search, 'status' => $status, 'page' => $page]);
$sidebarLinks = admin_sidebar_links('trips');

$pageTitle = 'Gestion de viajes';

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
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/viaje-form.php') ?>">Nuevo viaje</a>
        </div>
    </aside>

    <div class="admin-dashboard">
        <section class="admin-hero-card card">
            <div class="card-body admin-hero-card-body">
                <div>
                    <span class="admin-hero-badge">Trip management</span>
                    <h1>Gestion de viajes</h1>
                    <p class="muted">Control de trayectos, estados, plazas e imagenes dentro del mismo lenguaje visual del panel.</p>
                </div>
                <div class="admin-hero-meta">
                    <div class="admin-hero-meta-card">
                        <span>Modulo</span>
                        <strong>Viajes</strong>
                    </div>
                    <div class="admin-hero-meta-card">
                        <span>Accion</span>
                        <strong><?= (int) $result['total'] ?> registros</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="card admin-panel-card">
            <div class="card-body">
            <form class="admin-filter-bar" method="get">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Buscar por titulo, ruta o conductor">
                <select name="status">
                    <option value="">Todos los estados</option>
                    <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Publicados</option>
                    <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Borrador</option>
                </select>
                <button class="button button-primary" type="submit">Filtrar</button>
                <a class="button button-ghost" href="<?= app_url('/admin/viajes.php') ?>">Limpiar</a>
            </form>
            <p class="muted admin-results-copy"><?= (int) $result['total'] ?> viajes encontrados<?= $search !== '' || $status !== '' ? ' con los filtros aplicados' : '' ?>.</p>

            <?php if (!$result['items']): ?>
                <article class="card empty-state">
                    <div class="card-body">No hay viajes para mostrar.</div>
                </article>
            <?php else: ?>
                <div class="admin-list">
                    <?php foreach ($result['items'] as $item): ?>
                        <article class="admin-row">
                            <div class="admin-row-main">
                                <strong><?= e((string) $item['name']) ?></strong>
                                <span class="muted"><?= e((string) $item['origin']) ?> → <?= e((string) $item['destination']) ?> · <?= e(format_datetime((string) $item['departure_at'])) ?></span>
                                <div class="meta-row">
                                    <span class="pill"><?= e((string) $item['vehicle']) ?></span>
                                    <span class="pill"><?= remaining_seats($item) ?> plazas libres</span>
                                    <span class="pill price-pill"><?= e(trip_price_label($item['price_per_seat'] ?? 0)) ?></span>
                                    <span class="pill status-<?= e((string) $item['status']) ?>"><?= e(trip_status_label((string) $item['status'])) ?></span>
                                </div>
                            </div>
                            <div class="admin-row-actions">
                                <a class="button button-primary" href="<?= app_url('/admin/viaje-form.php?id=' . (int) $item['id']) ?>">Editar</a>
                                <form method="post" action="<?= app_url('/admin/actions/update_trip.php') ?>">
                                    <input type="hidden" name="trip_id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="action_name" value="toggle">
                                    <button class="button button-ghost" type="submit"><?= $item['status'] === 'published' ? 'Pasar a borrador' : 'Publicar' ?></button>
                                </form>
                                <form method="post" action="<?= app_url('/admin/actions/update_trip.php') ?>" onsubmit="return confirm('Se eliminara el viaje si no tiene reservas activas.');">
                                    <input type="hidden" name="trip_id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="action_name" value="delete">
                                    <button class="button button-danger" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($result['pages'] > 1): ?>
                    <nav class="admin-pagination" aria-label="Paginacion">
                        <?php for ($i = 1; $i <= (int) $result['pages']; $i++): ?>
                            <a href="<?= e(app_url('/admin/viajes.php?search=' . urlencode($search) . '&status=' . urlencode($status) . '&page=' . $i)) ?>"<?= $i === (int) $result['page'] ? ' aria-current="page"' : '' ?>><?= $i ?></a>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
