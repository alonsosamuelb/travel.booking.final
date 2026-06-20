<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Contacto';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="legal-page-shell">
    <article class="card legal-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Atencion</span>
            <h1>Contacto</h1>
            <p class="muted">Si tienes dudas sobre reservas, publicaciones de viajes o incidencias, puedes escribirnos desde este formulario.</p>
            <div class="toolbar">
                <a class="button button-ghost" href="<?= app_url('/index.php') ?>">Volver a inicio</a>
            </div>

            <div class="contact-grid">
                <form class="form-grid" method="post" action="<?= app_url('/actions/contact.php') ?>">
                    <div class="field-row">
                        <label>
                            Nombre
                            <input type="text" name="name" required>
                        </label>
                        <label>
                            Correo electronico
                            <input type="email" name="email" required>
                        </label>
                    </div>

                    <label>
                        Asunto
                        <input type="text" name="subject" required>
                    </label>

                    <label>
                        Mensaje
                        <textarea name="message" required placeholder="Cuéntanos tu consulta o incidencia."></textarea>
                    </label>

                    <div class="toolbar">
                        <button class="button button-primary" type="submit">Enviar mensaje</button>
                    </div>
                </form>

                <div class="contact-side">
                    <article class="contact-panel">
                        <h2>Canales principales</h2>
                        <strong>Correo</strong>
                        <p>soporte@travelbooking.local</p>
                    </article>
                    <article class="contact-panel">
                        <strong>Telefono</strong>
                        <p>+34 900 123 456</p>
                    </article>
                    <article class="contact-panel">
                        <strong>Horario</strong>
                        <p>Lunes a viernes, de 09:00 a 18:00.</p>
                    </article>
                    <article class="contact-panel">
                        <strong>Oficina</strong>
                        <p>Gran Via 128, Madrid.</p>
                    </article>
                </div>
            </div>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
