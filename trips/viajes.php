<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Viajes';
$filters = [
    'search' => $_GET['search'] ?? '',
    'origin' => $_GET['origin'] ?? '',
    'destination' => $_GET['destination'] ?? '',
    'departure_date' => $_GET['departure_date'] ?? '',
    'seats_min' => $_GET['seats_min'] ?? 1,
    'vehicle_group' => $_GET['vehicle_group'] ?? '',
];
$trips = fetch_trips($filters);
$vehicleGroup = (string) $filters['vehicle_group'];
$hasFilters = trim((string) $filters['origin']) !== ''
    || trim((string) $filters['search']) !== ''
    || trim((string) $filters['destination']) !== ''
    || trim((string) $filters['departure_date']) !== ''
    || (int) $filters['seats_min'] !== 1;
$pageTitle = $vehicleGroup === 'van' ? 'Viajes van' : ($vehicleGroup === 'car' ? 'Viajes coche' : 'Viajes');
$resultsTitle = $vehicleGroup === 'van' ? 'Vans disponibles' : ($vehicleGroup === 'car' ? 'Coches disponibles' : 'Viajes disponibles');
$resetUrl = $vehicleGroup !== ''
    ? app_url('/trips/viajes.php?vehicle_group=' . urlencode($vehicleGroup))
    : app_url('/trips/viajes.php');

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="search-hero">
    <div class="search-hero-head">
        <h1>¿A donde quieres ir?</h1>
        <p class="muted search-hero-copy">
            <?= $vehicleGroup === 'van'
                ? 'Estas buscando solo viajes en van.'
                : ($vehicleGroup === 'car'
                    ? 'Estas buscando solo viajes en coche.'
                    : 'Aqui buscas entre viajes de coche y van a la vez.') ?>
        </p>
    </div>

    <div class="search-mode-switch" aria-label="Tipo de viaje">
        <a href="<?= app_url('/trips/viajes.php') ?>"<?= $vehicleGroup === '' ? ' aria-current="page"' : '' ?>>Todos</a>
        <a href="<?= app_url('/trips/viajes.php?vehicle_group=car') ?>"<?= $vehicleGroup === 'car' ? ' aria-current="page"' : '' ?>>Coche</a>
        <a href="<?= app_url('/trips/viajes.php?vehicle_group=van') ?>"<?= $vehicleGroup === 'van' ? ' aria-current="page"' : '' ?>>Van</a>
    </div>

    <form class="search-minimal-bar" method="get">
        <?php if ($vehicleGroup !== ''): ?>
            <input type="hidden" name="vehicle_group" value="<?= e($vehicleGroup) ?>">
        <?php endif; ?>
        <label class="search-minimal-field">
            <span>Busqueda libre</span>
            <input type="text" name="search" value="<?= e((string) $filters['search']) ?>" placeholder="Nombre o descripcion">
        </label>
        <label class="search-minimal-field">
            <span>Origen</span>
            <input type="text" name="origin" value="<?= e((string) $filters['origin']) ?>" placeholder="Ciudad, estacion, lugar">
        </label>
        <label class="search-minimal-field">
            <span>Destino</span>
            <input type="text" name="destination" value="<?= e((string) $filters['destination']) ?>" placeholder="Ciudad, estacion, lugar">
        </label>
        <label class="search-minimal-field compact">
            <span>Fecha</span>
            <input type="date" name="departure_date" value="<?= e((string) $filters['departure_date']) ?>">
        </label>
        <label class="search-minimal-field compact">
            <span>Pasajeros</span>
            <input type="number" name="seats_min" min="1" value="<?= (int) $filters['seats_min'] ?>">
        </label>
        <button class="button button-primary search-minimal-button" type="submit">Buscar</button>
    </form>
</section>

<section class="section-head search-results-block">
    <div>
        <h2><?= e($resultsTitle) ?></h2>
        <p class="muted"><?= count($trips) ?> viajes publicados encontrados<?= $hasFilters ? ' con los filtros aplicados' : ' para esta categoria' ?>. Reservar en la app cuesta 1,00 € y el resto se paga al conductor por <?= e(off_platform_payment_methods_label()) ?>.</p>
    </div>
    <?php if ($hasFilters): ?>
        <a class="button button-ghost" href="<?= e($resetUrl) ?>">Limpiar filtros</a>
    <?php endif; ?>
</section>

<?php if (!$trips): ?>
    <article class="card empty-state">
        <div class="card-body">No hay viajes disponibles con esos filtros.</div>
    </article>
<?php else: ?>
    <section class="cards-grid cards-3">
        <?php foreach ($trips as $trip): ?>
            <?php $driverSummary = !empty($trip['creator_user_id']) ? user_rating_summary((int) $trip['creator_user_id']) : ['count' => 0, 'average' => 0]; ?>
            <?php $driverRoundedAverage = (int) round((float) $driverSummary['average']); ?>
            <article class="card trip-card">
                <a class="trip-card-media-link" href="<?= e(trip_detail_url($trip)) ?>">
                    <img class="trip-cover" src="<?= e(destination_image($trip['destination'] ?? null, $trip['image_path'] ?? null)) ?>" alt="<?= e($trip['name']) ?>">
                </a>
                <div class="card-body">
                    <h3><a class="trip-card-title-link" href="<?= e(trip_detail_url($trip)) ?>"><?= e($trip['name']) ?></a></h3>
                    <p class="muted"><?= e($trip['origin']) ?> → <?= e($trip['destination']) ?></p>
                    <div class="trip-card-driver">
                        <div class="trip-card-driver-main">
                            <span class="trip-card-driver-label">Conductor</span>
                            <?php if (!empty($trip['creator_user_id'])): ?>
                                <a class="trip-card-driver-name" href="<?= app_url('/account/usuario.php?id=' . (int) $trip['creator_user_id'] . '&trip_id=' . (int) $trip['id']) ?>"><?= e(trip_driver_name($trip)) ?></a>
                            <?php else: ?>
                                <strong class="trip-card-driver-name static"><?= e(trip_driver_name($trip)) ?></strong>
                            <?php endif; ?>
                        </div>
                        <?php if ($driverSummary['count'] > 0): ?>
                            <div class="trip-card-driver-rating">
                                <div class="rating-stars-view rating-stars-view-compact" aria-label="Valoracion media <?= e((string) $driverSummary['average']) ?> de 5">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="rating-star<?= $i <= $driverRoundedAverage ? ' is-filled' : '' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="trip-card-driver-score"><?= e((string) $driverSummary['average']) ?>/5</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="meta-row">
                        <span class="pill"><?= e(format_datetime($trip['departure_at'])) ?></span>
                        <span class="pill"><?= e($trip['vehicle']) ?></span>
                        <span class="pill"><?= remaining_seats($trip) ?> plazas libres</span>
                        <span class="pill price-pill"><?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?></span>
                    </div>
                    <p class="muted"><?= e($trip['description']) ?></p>
                    <div class="toolbar">
                        <a class="button button-ghost" href="<?= e(trip_detail_url($trip)) ?>">Ver detalles</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
