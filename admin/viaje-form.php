<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$tripId = max(0, (int) ($_GET['id'] ?? 0));
$trip = $tripId > 0 ? find_trip($tripId) : null;
$sidebarLinks = admin_sidebar_links('trips');

if ($tripId > 0 && !$trip) {
    set_flash('error', 'No se encontro el viaje a editar.');
    redirect_to(app_url('/admin/viajes.php'));
}

$userOptions = admin_user_options();
$pageTitle = $trip ? 'Editar viaje' : 'Nuevo viaje';

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
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/viajes.php') ?>">Volver a viajes</a>
        </div>
    </aside>

    <div class="admin-dashboard">
    <section class="admin-hero-card card">
        <div class="card-body admin-hero-card-body">
            <div>
                <span class="admin-hero-badge">Trip editor</span>
                <h1><?= $trip ? 'Editar viaje' : 'Crear viaje' ?></h1>
                <p class="muted">El administrador puede ajustar rutas, conductor, plazas, estado e imagen.</p>
            </div>
            <div class="admin-hero-meta">
                <div class="admin-hero-meta-card">
                    <span>Modulo</span>
                    <strong>Viajes</strong>
                </div>
                <div class="admin-hero-meta-card">
                    <span>Modo</span>
                    <strong><?= $trip ? 'Edicion' : 'Alta' ?></strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card admin-panel-card">
        <div class="card-body">
            <form class="form-grid" method="post" action="<?= app_url('/admin/actions/save_trip.php') ?>" enctype="multipart/form-data">
                <?php if ($trip): ?>
                    <input type="hidden" name="trip_id" value="<?= (int) $trip['id'] ?>">
                <?php endif; ?>

                <label>
                    Titulo del viaje
                    <input type="text" name="name" value="<?= e((string) ($trip['name'] ?? '')) ?>" required>
                </label>

                <label>
                    Descripcion
                    <textarea name="description" required><?= e((string) ($trip['description'] ?? '')) ?></textarea>
                </label>

                <div class="field-row">
                    <label>
                        Origen
                        <input type="text" name="origin" value="<?= e((string) ($trip['origin'] ?? '')) ?>" required>
                    </label>
                    <label>
                        Destino
                        <input type="text" name="destination" value="<?= e((string) ($trip['destination'] ?? '')) ?>" required>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        Fecha y hora de salida
                        <input type="datetime-local" name="departure_at" value="<?= !empty($trip['departure_at']) ? e((new DateTimeImmutable((string) $trip['departure_at']))->format('Y-m-d\TH:i')) : '' ?>" required>
                    </label>
                    <label>
                        Vehiculo
                        <input type="text" name="vehicle" value="<?= e((string) ($trip['vehicle'] ?? '')) ?>" required>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        Plazas disponibles
                        <input type="number" name="available_seats" min="1" max="9" value="<?= (int) ($trip['available_seats'] ?? 3) ?>" required>
                    </label>
                    <label>
                        Precio por plaza
                        <input type="number" name="price_per_seat" min="0.5" step="0.01" value="<?= e(isset($trip['price_per_seat']) ? number_format((float) $trip['price_per_seat'], 2, '.', '') : '12.00') ?>" required>
                    </label>
                </div>
                <p class="muted">Este precio lo acuerdan conductor y pasajero fuera de la app. La plataforma solo cobra 1,00 € por reserva online.</p>

                <div class="field-row">
                    <label>
                        Estado
                        <select name="status" required>
                            <option value="published"<?= (($trip['status'] ?? 'published') === 'published') ? ' selected' : '' ?>>Publicado</option>
                            <option value="draft"<?= (($trip['status'] ?? '') === 'draft') ? ' selected' : '' ?>>Borrador</option>
                        </select>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        Conductor responsable
                        <select name="creator_user_id" required>
                            <?php foreach ($userOptions as $userOption): ?>
                                <option value="<?= (int) $userOption['id'] ?>"<?= ((int) ($trip['creator_user_id'] ?? 0) === (int) $userOption['id']) ? ' selected' : '' ?>>
                                    <?= e((string) $userOption['full_name']) ?> · <?= e((string) $userOption['email']) ?><?= !empty($userOption['deleted_at']) ? ' (inactivo)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        URL de imagen
                        <input type="url" name="image_path" value="<?= e((string) ($trip['image_path'] ?? '')) ?>" placeholder="https://...">
                    </label>
                </div>

                <label>
                    Subir imagen
                    <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp">
                </label>

                <div class="toolbar">
                    <button class="button button-primary" type="submit"><?= $trip ? 'Guardar viaje' : 'Crear viaje' ?></button>
                    <a class="button button-ghost" href="<?= app_url('/admin/admin.php') ?>">Volver al panel</a>
                </div>
            </form>
        </div>
    </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
