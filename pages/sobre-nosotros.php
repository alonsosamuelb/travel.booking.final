<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Sobre nosotros';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="legal-page-shell">
    <article class="card legal-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Proyecto</span>
            <h1>Sobre nosotros</h1>
            <p>Travel Booking es una plataforma centrada en viajes compartidos donde conductores y viajeros pueden encontrarse, publicar trayectos y gestionar reservas con una interfaz sencilla.</p>
            <div class="toolbar">
                <a class="button button-ghost" href="<?= app_url('/index.php') ?>">Volver a inicio</a>
            </div>

            <h2>Que hacemos</h2>
            <p>Facilitamos la publicacion de viajes, la busqueda de trayectos y la formalizacion de reservas con un flujo accesible, claro y adaptable tanto a entorno local como a un despliegue real.</p>

            <h2>Nuestro enfoque</h2>
            <p>Priorizamos una arquitectura simple, una gestion administrativa util y un flujo comprensible para usuarios y administradores, sin perder la filosofia de una web de reservas de viajes.</p>

            <h2>Autor del proyecto</h2>
            <p>Proyecto desarrollado por Samuel Buitrago Alonso como entrega intermodular adaptada al dominio de viajes compartidos.</p>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
