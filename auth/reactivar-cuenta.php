<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect_to(app_url('/trips/viajes.php'));
}

$pageTitle = 'Reactivar cuenta';
$prefillEmail = trim((string) ($_GET['email'] ?? ''));

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="auth-page-shell">
    <article class="card auth-page-card">
        <div class="card-body">
            <span class="detail-eyebrow">Cuenta inactiva</span>
            <h1>Reactivar cuenta</h1>
            <p class="muted">Si tu cuenta estaba dada de baja logicamente, puedes activarla de nuevo con tus credenciales.</p>

            <form class="form-grid" method="post" action="<?= app_url('/actions/reactivate_account.php') ?>">
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
                <button class="button button-primary" type="submit">Reactivar mi cuenta</button>
            </form>

            <p class="auth-page-switch">¿Prefieres volver al acceso? <a href="<?= app_url('/auth/login.php') ?>">Inicia sesion aqui</a>.</p>
        </div>
    </article>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
