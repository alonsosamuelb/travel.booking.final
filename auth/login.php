<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect_to(app_url('/trips/viajes.php'));
}

$pageTitle = 'Iniciar sesion';
$prefillEmail = trim((string) ($_GET['email'] ?? ''));

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="auth-page-shell">
    <article class="card auth-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Acceso</span>
            <h1>Iniciar sesion</h1>
            <p class="muted">Entra a tu cuenta para gestionar reservas, publicar viajes y editar tu perfil.</p>

            <form class="form-grid" method="post" action="<?= app_url('/actions/login.php') ?>">
                <label>
                    Correo electronico
                    <input type="email" name="email" value="<?= e($prefillEmail) ?>" required>
                </label>
                <label class="password-field">
                    Contrasena
                    <div class="password-input-wrap">
                        <input type="password" name="password" minlength="6" required data-password-input>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar u ocultar contrasena">Ver</button>
                    </div>
                </label>
                <button class="button button-primary" type="submit">Entrar</button>
            </form>

            <div class="auth-page-links">
                <p class="auth-page-switch">¿Aun no tienes cuenta? <a href="<?= app_url('/auth/registro.php') ?>">Registrate aqui</a>.</p>
                <p class="auth-page-switch"><a href="<?= app_url('/auth/olvide-contrasena.php') ?>">He olvidado mi contrasena</a></p>
            </div>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
