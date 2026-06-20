<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect_to(app_url('/account/perfil.php'));
}

$pageTitle = 'Recuperar acceso';
$prefillEmail = trim((string) ($_GET['email'] ?? ''));

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="auth-page-shell">
    <article class="card auth-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Recuperacion</span>
            <h1>He olvidado mi contrasena</h1>
            
            <form class="form-grid" method="post" action="<?= app_url('/actions/forgot_password.php') ?>">
                <label>
                    Correo electronico
                    <input type="email" name="email" value="<?= e($prefillEmail) ?>" required>
                </label>
                <label class="password-field">
                    Nueva contrasena
                    <div class="password-input-wrap">
                        <input type="password" name="password" minlength="6" required data-password-input>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar u ocultar contrasena">Ver</button>
                    </div>
                </label>
                <label class="password-field">
                    Repite la contrasena
                    <div class="password-input-wrap">
                        <input type="password" name="password_confirmation" minlength="6" required data-password-input>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar u ocultar contrasena">Ver</button>
                    </div>
                </label>
                <button class="button button-primary" type="submit">Actualizar contrasena</button>
            </form>

            <p class="auth-page-switch">¿Recuerdas tu acceso? <a href="<?= app_url('/auth/login.php') ?>">Volver al login</a>.</p>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
