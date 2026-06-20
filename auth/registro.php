<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect_to(app_url('/trips/publicar-viaje.php'));
}

$pageTitle = 'Registrarse';
$prefillEmail = trim((string) ($_GET['email'] ?? ''));

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="auth-page-shell">
    <article class="card auth-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Cuenta nueva</span>
            <h1>Crear cuenta</h1>
            <p class="muted">Registra tu perfil para reservar plazas, publicar viajes y dejar notas para otros viajeros.</p>

            <form class="form-grid" method="post" action="<?= app_url('/actions/register.php') ?>">
                <label>
                    Nombre completo
                    <input type="text" name="full_name" required>
                </label>
                <label>
                    Correo electronico
                    <input type="email" name="email" value="<?= e($prefillEmail) ?>" required>
                </label>
                <label>
                    Telefono
                    <input type="text" name="phone" placeholder="Opcional">
                </label>
                <label class="password-field">
                    Contrasena
                    <div class="password-input-wrap">
                        <input type="password" name="password" minlength="6" required data-password-input>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar u ocultar contrasena">Ver</button>
                    </div>
                </label>
                <button class="button button-warm" type="submit">Registrarme</button>
            </form>

            <p class="auth-page-switch">¿Ya tienes cuenta? <a href="<?= app_url('/auth/login.php') ?>">Inicia sesion aqui</a>.</p>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
