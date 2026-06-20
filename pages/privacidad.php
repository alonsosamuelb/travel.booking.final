<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Privacidad';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="legal-page-shell">
    <article class="card legal-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Legal</span>
            <h1>Politica de privacidad</h1>
            <p>En Travel Booking tratamos los datos personales necesarios para gestionar cuentas de usuario, viajes publicados, reservas, opiniones y comunicaciones relacionadas con el servicio.</p>
            <div class="toolbar">
                <a class="button button-ghost" href="<?= app_url('/index.php') ?>">Volver a inicio</a>
            </div>

            <h2>Datos que tratamos</h2>
            <ul>
                <li>Datos de acceso y perfil, como nombre, correo y telefono.</li>
                <li>Datos relacionados con viajes, reservas y mensajes enviados desde formularios.</li>
                <li>Informacion tecnica basica necesaria para el funcionamiento de la aplicacion.</li>
            </ul>

            <h2>Finalidad</h2>
            <p>Usamos estos datos para permitir el uso normal de la plataforma, gestionar reservas, mostrar informacion de conductores y viajeros, atender consultas y mantener la seguridad basica del sistema.</p>

            <h2>Conservacion</h2>
            <p>Los datos se conservan mientras la cuenta siga activa o mientras sea necesario para mantener historico de reservas, opiniones o obligaciones del servicio.</p>

            <h2>Derechos</h2>
            <p>El usuario puede revisar y actualizar parte de su informacion desde su perfil. Para consultas adicionales puede usar la pagina de contacto.</p>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
