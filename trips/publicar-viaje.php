<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Publicar viaje';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Publica tu viaje de manera segura</h1>
    </div>
</section>

<article class="card">
    <div class="card-body">
        <form class="form-grid" method="post" action="<?= app_url('/actions/create_trip.php') ?>" enctype="multipart/form-data">
            <label>
                Titulo del viaje
                <input type="text" name="name" required>
            </label>
            <label>
                Descripcion
                <textarea name="description" required placeholder="Explica el trayecto, tipo de equipaje o condiciones relevantes"></textarea>
            </label>
            <div class="field-row">
                <label>
                    Origen
                    <input type="text" name="origin" required>
                </label>
                <label>
                    Destino
                    <input type="text" name="destination" required>
                </label>
            </div>
            <div class="field-row">
                <label>
                    Fecha y hora de salida
                    <input type="datetime-local" name="departure_at" required>
                </label>
                <label>
                    Vehiculo
                    <input type="text" name="vehicle" required>
                </label>
            </div>
            <div class="field-row">
                <label>
                    Plazas disponibles
                    <input type="number" name="available_seats" min="1" max="9" value="3" required>
                </label>
                <label>
                    Precio orientativo por plaza
                    <input type="number" name="price_per_seat" min="0.5" step="0.01" value="12.00" required>
                </label>
            </div>
            <p class="muted">Este importe es el que el pasajero abonara directamente al conductor por <?= e(off_platform_payment_methods_label()) ?>. La app solo cobra 1,00 € por formalizar la reserva.</p>
            <div class="field-row">
                <label>
                    Subir imagen
                    <input type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp">
                </label>
                <label>
                    URL de imagen
                    <input type="url" name="image_path" placeholder="https://...">
                </label>
            </div>
            <p class="muted">Puedes subir una imagen desde tu equipo o pegar una URL. Si rellenas ambas, se usara la imagen subida.</p>
            <div class="toolbar">
                <button class="button button-primary" type="submit">Guardar viaje</button>
                <a class="button button-ghost" href="<?= app_url('/trips/mis-viajes.php') ?>">Ir a mis viajes</a>
            </div>
        </form>
    </div>
</article>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
