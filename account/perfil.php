<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$pageTitle = 'Mi perfil';
$user = current_user();
$ratingSummary = user_rating_summary((int) ($user['id'] ?? 0));
$roundedAverage = (int) round((float) $ratingSummary['average']);

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Editar perfil</h1>
        <p class="muted">Actualiza tus datos principales para que los demas viajeros te identifiquen mejor.</p>
    </div>
</section>

<section class="profile-shell">
    <article class="card profile-card">
        <div class="card-body">
            <form class="form-grid profile-form" method="post" action="<?= app_url('/actions/update_profile.php') ?>">
                <label>
                    Nombre completo
                    <input type="text" name="full_name" value="<?= e((string) ($user['full_name'] ?? '')) ?>" required>
                </label>
                <label>
                    Correo electronico
                    <input type="email" name="email" value="<?= e((string) ($user['email'] ?? '')) ?>" required>
                </label>
                <label>
                    Telefono
                    <input type="text" name="phone" value="<?= e((string) ($user['phone'] ?? '')) ?>" placeholder="Opcional">
                </label>
                <div class="toolbar">
                    <button class="button button-primary" type="submit">Guardar cambios</button>
                    <a class="button button-ghost" href="<?= app_url('/trips/viajes.php') ?>">Volver a viajes</a>
                </div>
            </form>
            <form class="profile-danger-form" method="post" action="<?= app_url('/actions/deactivate_account.php') ?>" onsubmit="return confirm('Tu cuenta quedara inactiva y saldras de la sesion.');">
                <button class="button button-danger" type="submit">Dar de baja mi cuenta</button>
            </form>
        </div>
    </article>
    <aside class="card profile-side-card">
        <div class="card-body">
            <h2>Tu cuenta</h2>
            <p class="muted">Estos datos se usan para tus publicaciones, reservas y para mostrar informacion basica del conductor en cada viaje.</p>
            <div class="meta-row">
                <span class="pill"><?= e((string) ($user['role'] ?? 'user')) ?></span>
                <?php if (!empty($user['phone'])): ?>
                    <span class="pill"><?= e((string) $user['phone']) ?></span>
                <?php endif; ?>
            </div>
            <div class="profile-review-box">
                <span class="trip-driver-label">Opiniones recibidas</span>
                <?php if ($ratingSummary['count'] > 0): ?>
                    <strong><?= e((string) $ratingSummary['average']) ?>/5 · <?= (int) $ratingSummary['count'] ?> opiniones</strong>
                    <div class="rating-stars-view" aria-label="Valoracion media <?= e((string) $ratingSummary['average']) ?> de 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="rating-star<?= $i <= $roundedAverage ? ' is-filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <a class="button button-ghost" href="<?= app_url('/account/usuario.php?id=' . (int) $user['id']) ?>">Ver mis opiniones</a>
                <?php else: ?>
                    <strong>Sin valoraciones todavia</strong>
                    <p class="muted">Cuando completes viajes y otros usuarios te valoren, podras revisarlas aqui.</p>
                <?php endif; ?>
            </div>
        </div>
    </aside>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
