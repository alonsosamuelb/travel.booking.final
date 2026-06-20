<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$reservationId = max(0, (int) ($_GET['id'] ?? 0));
$reservation = $reservationId > 0 ? find_user_reservation($reservationId, (int) current_user()['id']) : null;

if (!$reservation) {
    set_flash('error', 'No puedes editar esa reserva.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

if (!user_can_edit_reservation($reservation)) {
    set_flash('error', 'Solo puedes editar reservas activas antes de la salida.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$pageTitle = 'Editar reserva';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Edita tu reserva</h1>
        <p class="muted">Puedes ajustar solo tus plazas reservadas y la nota para el conductor. La ruta, la salida y el precio los define el conductor.</p>
    </div>
    <a class="button button-ghost" href="<?= app_url('/trips/mis-reservas.php') ?>">Volver a mis reservas</a>
</section>

<article class="card">
    <div class="card-body">
        <form class="form-grid" method="post" action="<?= app_url('/actions/update_reservation.php') ?>">
            <input type="hidden" name="reservation_id" value="<?= (int) $reservation['id'] ?>">

            <label>
                Viaje
                <input type="text" value="<?= e((string) $reservation['trip_name']) ?>" readonly class="input-readonly">
            </label>

            <div class="field-row">
                <label>
                    Origen
                    <input type="text" value="<?= e((string) $reservation['origin']) ?>" readonly class="input-readonly">
                </label>
                <label>
                    Destino
                    <input type="text" value="<?= e((string) $reservation['destination']) ?>" readonly class="input-readonly">
                </label>
            </div>

            <div class="field-row">
                <label>
                    Fecha y hora de salida
                    <input type="text" value="<?= e(format_datetime((string) $reservation['departure_at'])) ?>" readonly class="input-readonly">
                </label>
                <label>
                    Precio orientativo por plaza
                    <input type="text" value="<?= e(trip_price_label($reservation['price_per_seat'] ?? 0)) ?>" readonly class="input-readonly">
                </label>
            </div>

            <div class="field-row">
                <label>
                    Plazas reservadas
                    <input type="number" name="seats_reserved" min="1" max="9" value="<?= (int) $reservation['seats_reserved'] ?>" required>
                </label>
                <label>
                    Cuota app
                    <input type="text" value="<?= e(reservation_total_label($reservation['total_price'] ?? platform_booking_fee())) ?>" readonly class="input-readonly">
                </label>
            </div>

            <label>
                Nota para el conductor
                <textarea name="notes" placeholder="Actualiza equipaje, punto de encuentro o cualquier detalle util"><?= e((string) ($reservation['notes'] ?? '')) ?></textarea>
            </label>

            <p class="muted">La cuota de la plataforma sigue siendo 1,00 €. El resto se acuerda y paga fuera de la app por <?= e(off_platform_payment_methods_label()) ?>.</p>

            <div class="toolbar">
                <button class="button button-primary" type="submit">Guardar reserva</button>
                <a class="button button-ghost" href="<?= app_url('/trips/viaje.php?id=' . (int) $reservation['trip_id']) ?>">Volver al viaje</a>
            </div>
        </form>
    </div>
</article>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
