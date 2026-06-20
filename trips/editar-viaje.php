<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$tripId = max(0, (int) ($_GET['id'] ?? 0));
$trip = $tripId > 0 ? find_trip($tripId) : null;

if (!$trip || (int) ($trip['creator_user_id'] ?? 0) !== (int) current_user()['id']) {
    set_flash('error', 'No puedes editar ese viaje.');
    redirect_to(app_url('/trips/mis-viajes.php'));
}

$pageTitle = 'Editar viaje';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Edita tu viaje</h1>
        <p class="muted">Puedes actualizar los datos de presentacion de tu viaje, pero la salida, la ruta y el precio quedan fijados para mantener la coherencia de la reserva.</p>
    </div>
    <a class="button button-ghost" href="<?= app_url('/trips/mis-viajes.php') ?>">Volver a mis viajes</a>
</section>

<article class="card">
    <div class="card-body">
        <form class="form-grid" method="post" action="<?= app_url('/actions/update_trip.php') ?>" enctype="multipart/form-data">
            <input type="hidden" name="trip_id" value="<?= (int) $trip['id'] ?>">

            <label>
                Titulo del viaje
                <input type="text" name="name" value="<?= e((string) $trip['name']) ?>" required>
            </label>

            <label>
                Descripcion
                <textarea name="description" required placeholder="Explica el trayecto, tipo de equipaje o condiciones relevantes"><?= e((string) $trip['description']) ?></textarea>
            </label>

            <div class="field-row">
                <label>
                    Origen
                    <input type="text" value="<?= e((string) $trip['origin']) ?>" readonly class="input-readonly">
                </label>
                <label>
                    Destino
                    <input type="text" value="<?= e((string) $trip['destination']) ?>" readonly class="input-readonly">
                </label>
            </div>

            <div class="field-row">
                <label>
                    Fecha y hora de salida
                    <input type="text" value="<?= e(format_datetime((string) $trip['departure_at'])) ?>" readonly class="input-readonly">
                </label>
                <label>
                    Vehiculo
                    <input type="text" name="vehicle" value="<?= e((string) $trip['vehicle']) ?>" required>
                </label>
            </div>

            <div class="field-row">
                <label>
                    Plazas disponibles
                    <input type="number" name="available_seats" min="1" max="9" value="<?= (int) $trip['available_seats'] ?>" required>
                </label>
                <label>
                    Precio orientativo por plaza
                    <input type="text" value="<?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?>" readonly class="input-readonly">
                </label>
            </div>

            <p class="muted">La salida, la ruta y el precio no se pueden modificar desde esta edicion. Ese importe es el que el pasajero abonara directamente al conductor por <?= e(off_platform_payment_methods_label()) ?>. La app solo cobra 1,00 € por formalizar la reserva.</p>

            <div class="field-row">
                <label>
                    Subir nueva imagen
                    <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp">
                </label>
                <label>
                    URL de imagen
                    <input type="url" name="image_path" value="<?= e((string) ($trip['image_path'] ?? '')) ?>" placeholder="https://...">
                </label>
            </div>

            <p class="muted">Si subes una imagen nueva, tendra prioridad sobre la URL existente. Si dejas ambos vacios, se conserva la imagen actual.</p>

            <div class="toolbar">
                <button class="button button-primary" type="submit">Guardar cambios</button>
                <a class="button button-ghost" href="<?= app_url('/trips/viaje.php?id=' . (int) $trip['id']) ?>">Volver al detalle</a>
            </div>
        </form>
    </div>
</article>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
