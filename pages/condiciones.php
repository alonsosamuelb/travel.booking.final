<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Condiciones';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="legal-page-shell">
    <article class="card legal-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Legal</span>
            <h1>Condiciones de uso</h1>
            <p>El uso de Travel Booking implica aceptar unas normas basicas de funcionamiento orientadas a la correcta publicacion de viajes, reserva de plazas y convivencia entre usuarios.</p>
            <div class="toolbar">
                <a class="button button-ghost" href="<?= app_url('/index.php') ?>">Volver a inicio</a>
            </div>

            <h2>Publicacion de viajes</h2>
            <p>Los conductores deben publicar informacion veraz sobre origen, destino, fecha, plazas disponibles y precio orientativo por plaza. La plataforma puede limitar o retirar publicaciones incoherentes o inadecuadas.</p>

            <h2>Reservas</h2>
            <p>Los viajeros pueden formalizar reservas segun la disponibilidad del viaje y las reglas activas de la aplicacion. La cuota online cubre la formalizacion de la reserva y el resto del importe se acuerda entre conductor y pasajero.</p>

            <h2>Pagos externos</h2>
            <p>El pago restante del trayecto se realiza fuera de la plataforma mediante el metodo acordado entre las partes, como efectivo, Bizum o transferencia.</p>

            <h2>Valoraciones y comportamiento</h2>
            <p>Tras un viaje completado, conductor y pasajero pueden valorarse. Se espera un uso responsable de la plataforma y un comportamiento respetuoso entre usuarios.</p>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
