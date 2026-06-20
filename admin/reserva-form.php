<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$reservationId = max(0, (int) ($_GET['id'] ?? 0));
$reservation = $reservationId > 0 ? admin_find_reservation($reservationId) : null;
$sidebarLinks = admin_sidebar_links('reservations');

if ($reservationId > 0 && !$reservation) {
    set_flash('error', 'No se encontro la reserva a editar.');
    redirect_to(app_url('/admin/reservas.php'));
}

$userOptions = admin_user_options();
$tripOptions = admin_trip_options();
$pageTitle = $reservation ? 'Editar reserva' : 'Nueva reserva';

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
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/reservas.php') ?>">Volver a reservas</a>
        </div>
    </aside>

    <div class="admin-dashboard">
    <section class="admin-hero-card card">
        <div class="card-body admin-hero-card-body">
            <div>
                <span class="admin-hero-badge">Reservation editor</span>
                <h1><?= $reservation ? 'Editar reserva' : 'Crear reserva' ?></h1>
                <p class="muted">El administrador puede reasignar usuario, viaje, plazas, estado y notas.</p>
            </div>
            <div class="admin-hero-meta">
                <div class="admin-hero-meta-card">
                    <span>Modulo</span>
                    <strong>Reservas</strong>
                </div>
                <div class="admin-hero-meta-card">
                    <span>Modo</span>
                    <strong><?= $reservation ? 'Edicion' : 'Alta' ?></strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card admin-panel-card">
        <div class="card-body">
            <form class="form-grid" method="post" action="<?= app_url('/admin/actions/save_reservation.php') ?>" data-admin-reservation-form>
                <?php if ($reservation): ?>
                    <input type="hidden" name="reservation_id" value="<?= (int) $reservation['id'] ?>">
                <?php endif; ?>

                <div class="field-row">
                    <label>
                        Usuario
                        <select name="user_id" required>
                            <?php foreach ($userOptions as $userOption): ?>
                                <option value="<?= (int) $userOption['id'] ?>"<?= ((int) ($reservation['user_id'] ?? 0) === (int) $userOption['id']) ? ' selected' : '' ?>>
                                    <?= e((string) $userOption['full_name']) ?> · <?= e((string) $userOption['email']) ?><?= !empty($userOption['deleted_at']) ? ' (inactivo)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Viaje
                        <select name="trip_id" required data-admin-trip-select>
                            <?php foreach ($tripOptions as $tripOption): ?>
                                <option
                                    value="<?= (int) $tripOption['id'] ?>"
                                    data-price="<?= e(number_format((float) ($tripOption['price_per_seat'] ?? 0), 2, '.', '')) ?>"
                                    <?= ((int) ($reservation['trip_id'] ?? 0) === (int) $tripOption['id']) ? ' selected' : '' ?>
                                >
                                    <?= e((string) $tripOption['name']) ?> · <?= e((string) $tripOption['origin']) ?> → <?= e((string) $tripOption['destination']) ?> · <?= e(format_datetime((string) $tripOption['departure_at'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        Plazas reservadas
                        <input type="number" name="seats_reserved" min="1" max="9" value="<?= (int) ($reservation['seats_reserved'] ?? 1) ?>" required data-admin-seats-input>
                    </label>
                    <label>
                        Estado
                        <select name="status" required>
                            <option value="active"<?= (($reservation['status'] ?? 'active') === 'active') ? ' selected' : '' ?>>Activa</option>
                            <option value="cancelled"<?= (($reservation['status'] ?? '') === 'cancelled') ? ' selected' : '' ?>>Cancelada</option>
                            <option value="completed"<?= (($reservation['status'] ?? '') === 'completed') ? ' selected' : '' ?>>Completada</option>
                        </select>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        Precio orientativo por plaza
                        <input type="number" name="price_per_seat" min="0.5" step="0.01" value="<?= e(isset($reservation['price_per_seat']) ? number_format((float) $reservation['price_per_seat'], 2, '.', '') : '') ?>" required data-admin-price-input>
                    </label>
                    <label>
                        Cuota plataforma
                        <input type="text" value="<?= isset($reservation['total_price']) ? e(format_money((float) $reservation['total_price'])) : '' ?>" readonly data-admin-total-output>
                    </label>
                </div>
                <p class="muted">La cuota online de la plataforma es fija: 1,00 €. El precio por plaza se usa solo como referencia para el pago externo al conductor.</p>

                <label>
                    Rol dentro de la reserva
                    <select name="travel_role" required>
                        <option value="passenger"<?= (($reservation['travel_role'] ?? 'passenger') === 'passenger') ? ' selected' : '' ?>>Pasajero</option>
                        <option value="driver"<?= (($reservation['travel_role'] ?? '') === 'driver') ? ' selected' : '' ?>>Conductor</option>
                    </select>
                </label>

                <label>
                    Estado del pago
                    <select name="payment_status" required>
                        <option value="pending"<?= (($reservation['payment_status'] ?? 'pending') === 'pending') ? ' selected' : '' ?>>Pendiente de pago</option>
                        <option value="paid"<?= (($reservation['payment_status'] ?? '') === 'paid') ? ' selected' : '' ?>>Pagada</option>
                        <option value="refunded"<?= (($reservation['payment_status'] ?? '') === 'refunded') ? ' selected' : '' ?>>Reembolsada</option>
                    </select>
                </label>

                <label>
                    Nota para el conductor
                    <textarea name="notes"><?= e((string) ($reservation['notes'] ?? '')) ?></textarea>
                </label>

                <div class="toolbar">
                    <button class="button button-primary" type="submit"><?= $reservation ? 'Guardar reserva' : 'Crear reserva' ?></button>
                    <a class="button button-ghost" href="<?= app_url('/admin/admin.php') ?>">Volver al panel</a>
                </div>
            </form>
        </div>
    </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
