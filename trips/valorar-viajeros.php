<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$tripId = (int) ($_GET['trip_id'] ?? 0);
$trip = $tripId > 0 ? find_trip($tripId) : null;

if (!$trip || (int) ($trip['creator_user_id'] ?? 0) !== (int) current_user()['id']) {
    set_flash('error', 'No puedes gestionar valoraciones para ese viaje.');
    redirect_to(app_url('/trips/mis-viajes.php'));
}

$reviewTargets = owner_completed_trip_reviews((int) current_user()['id'], $tripId);
$pageTitle = 'Valorar pasajeros';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="section-head">
    <div>
        <h1>Valorar pasajeros</h1>
        <p class="muted">Viaje completado: <?= e((string) $trip['name']) ?> · <?= e((string) $trip['origin']) ?> → <?= e((string) $trip['destination']) ?></p>
    </div>
    <a class="button button-ghost" href="<?= app_url('/trips/mis-viajes.php') ?>">Volver a mis viajes</a>
</section>

<?php if (!$reviewTargets): ?>
    <article class="card empty-state">
        <div class="card-body">Este viaje no tiene pasajeros completados para valorar.</div>
    </article>
<?php else: ?>
    <section class="card admin-panel-card">
        <div class="card-body">
            <div class="admin-list">
                <?php foreach ($reviewTargets as $item): ?>
                    <article class="admin-row">
                        <div class="admin-row-main">
                            <strong><?= e((string) $item['passenger_name']) ?></strong>
                            <span class="muted"><?= e((string) $item['passenger_email']) ?> · <?= (int) $item['seats_reserved'] ?> plazas</span>
                            <div class="meta-row">
                                <span class="pill"><?= e(payment_status_label((string) ($item['payment_status'] ?? 'pending'))) ?></span>
                                <?php if (!empty($item['existing_review'])): ?>
                                    <span class="pill"><?= (int) $item['existing_review']['rating'] ?>/5</span>
                                <?php else: ?>
                                    <span class="pill">Sin valorar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="admin-row-actions">
                            <a class="button button-primary" href="<?= app_url('/trips/valorar-reserva.php?reservation_id=' . (int) $item['id']) ?>">
                                <?= !empty($item['existing_review']) ? 'Editar valoracion' : 'Valorar pasajero' ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
