<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$reservationId = (int) ($_GET['reservation_id'] ?? 0);
$context = review_context_for_user($reservationId, (int) current_user()['id']);

if (!$context) {
    set_flash('error', 'No puedes valorar esa reserva.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$existingReview = user_review_for_pair(
    $reservationId,
    (int) current_user()['id'],
    (int) $context['reviewee_id']
);

$pageTitle = 'Valorar viaje';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Valorar experiencia</h1>
        <p class="muted">
            <?= (string) $context['mode'] === 'driver_to_passenger'
                ? 'Valora la experiencia con este pasajero una vez finalizado el viaje.'
                : 'Valora al conductor despues de completar tu reserva.' ?>
        </p>
    </div>
</section>

<article class="card">
    <div class="card-body">
        <div class="section-head" style="margin-bottom: 18px;">
            <div>
                <h2><?= e((string) $context['reservation']['trip_name']) ?></h2>
                <p class="muted"><?= e((string) $context['reservation']['origin']) ?> → <?= e((string) $context['reservation']['destination']) ?> · <?= e(format_datetime((string) $context['reservation']['departure_at'])) ?></p>
            </div>
        </div>

        <form class="form-grid" method="post" action="<?= app_url('/actions/save_review.php') ?>">
            <input type="hidden" name="reservation_id" value="<?= $reservationId ?>">

            <label>
                Persona valorada
                <input type="text" value="<?= e((string) $context['reviewee_name']) ?>" readonly>
            </label>

            <label>
                Puntuacion
                <select name="rating" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>"<?= ((int) ($existingReview['rating'] ?? 5) === $i) ? ' selected' : '' ?>><?= $i ?> / 5</option>
                    <?php endfor; ?>
                </select>
            </label>

            <label>
                Comentario
                <textarea name="comment" maxlength="600" placeholder="Cuenta brevemente como fue la experiencia."><?= e((string) ($existingReview['comment'] ?? '')) ?></textarea>
            </label>

            <div class="toolbar">
                <button class="button button-primary" type="submit"><?= $existingReview ? 'Actualizar valoracion' : 'Enviar valoracion' ?></button>
                <a class="button button-ghost" href="<?= (string) $context['mode'] === 'driver_to_passenger' ? app_url('/trips/valorar-viajeros.php?trip_id=' . (int) $context['reservation']['trip_id']) : app_url('/trips/mis-reservas.php') ?>">Volver</a>
            </div>
        </form>
    </div>
</article>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
