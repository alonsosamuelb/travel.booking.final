<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Inicio';
$featuredTrips = array_slice(fetch_trips(), 0, 3);
$heroTrip = $featuredTrips[0] ?? null;
$heroImage = random_project_hero_image(destination_image($heroTrip['destination'] ?? null, $heroTrip['image_path'] ?? null));

include __DIR__ . '/includes/header.php';
?>

<section class="homepage-hero">
    <div class="hero-copy">
        <span class="home-kicker">reservas entre personas</span>
        <h1>Viajes unicos y economicos compartiendo coche</h1>
        <p>Encuentra trayectos publicados por otros viajeros, reserva plazas de forma clara y publica tus propios viajes sin complicarte con procesos largos.</p>
    </div>

    <div class="hero-visual">
        <img src="<?= e($heroImage) ?>" alt="Destino destacado para viajar">
    </div>
</section>

<section class="home-search-wrap">
    <form class="home-search-bar" method="get" action="<?= app_url('/trips/viajes.php') ?>">
        <label class="home-search-field">
            <span>Origen</span>
            <input type="text" name="origin" placeholder="Ciudad, estacion, lugar">
        </label>
        <label class="home-search-field">
            <span>Destino</span>
            <input type="text" name="destination" placeholder="Ciudad, estacion, lugar">
        </label>
        <label class="home-search-field compact">
            <span>Fecha</span>
            <input type="date" name="departure_date">
        </label>
        <label class="home-search-field compact">
            <span>Pasajeros</span>
            <input type="number" name="seats_min" min="1" value="1">
        </label>
        <button class="button button-primary home-search-button" type="submit">Buscar</button>
    </form>
</section>

<section class="home-benefits">
    <article class="benefit-item">
        <div class="benefit-icon">↘</div>
        <h3>Viajes baratos</h3>
        <p>Vayas donde vayas, hay un viaje compartido que te puede llevar por menos y con un flujo de reserva claro.</p>
    </article>
    <article class="benefit-item">
        <div class="benefit-icon">✓</div>
        <h3>Seguro y sencillo</h3>
        <p>La app se centra en lo importante: buscar, publicar y reservar, con menos pasos y una experiencia facil de entender.</p>
    </article>
    <article class="benefit-item">
        <div class="benefit-icon">⌁</div>
        <h3>Cerca de ti</h3>
        <p>Filtrando por origen, destino y fecha puedes encontrar trayectos que se adapten mejor a tu ruta y a tu tiempo.</p>
    </article>
</section>

<section class="home-split">
    <div class="home-split-image">
        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=80" alt="Destino de viaje">
    </div>
    <div class="home-split-copy">
        <h2>¿A donde quieres viajar?</h2>
        <p>Compartir coche es una forma genial de viajar: es barato, comodo y mas facil de organizar cuando el flujo de busqueda y reserva esta pensado para ser directo.</p>
        <p>En esta version simplificada puedes explorar rutas, reservar plazas disponibles y publicar trayectos propios sin depender de una estructura complicada.</p>
        <div class="hero-actions">
            <a class="button button-primary" href="<?= app_url('/trips/viajes.php') ?>">Explorar viajes</a>
            <a class="button button-ghost" href="<?= app_url('/trips/publicar-viaje.php') ?>">Publicar un trayecto</a>
        </div>
    </div>
</section>

<section class="section-head" style="margin-top: 38px;">
    <div>
        <h2>Viajes destacados</h2>
        <p class="muted">Una muestra de viajes futuros publicados.</p>
    </div>
</section>

<?php if (!$featuredTrips): ?>
    <article class="card empty-state">
        <div class="card-body">No hay viajes destacados por ahora.</div>
    </article>
<?php else: ?>
    <section class="cards-grid cards-3">
        <?php foreach ($featuredTrips as $trip): ?>
            <?php $driverSummary = !empty($trip['creator_user_id']) ? user_rating_summary((int) $trip['creator_user_id']) : ['count' => 0, 'average' => 0]; ?>
            <?php $driverRoundedAverage = (int) round((float) $driverSummary['average']); ?>
            <article class="card trip-card featured-trip-card">
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
                        <span class="pill"><?= remaining_seats($trip) ?> plazas libres</span>
                        <span class="pill price-pill"><?= e(trip_price_label($trip['price_per_seat'] ?? 0)) ?></span>
                    </div>
                    <div class="toolbar">
                        <a class="button button-ghost" href="<?= e(trip_detail_url($trip)) ?>">Ver detalles</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!is_logged_in()): ?>
    <section class="auth-explainer" id="auth" aria-labelledby="auth-explainer-title">
        <div class="auth-explainer-head">
            <span class="section-accent"></span>
            <h2 id="auth-explainer-title">Como funciona Travel Booking</h2>
            <p class="muted">Dos recorridos dentro de la misma plataforma: reservar como pasajero o publicar trayectos como conductor.</p>
        </div>

        <div class="auth-explainer-grid">
            <article class="card auth-explainer-card">
                <div class="card-body">
                    <h3>Proceso de pasajero</h3>
                    <div class="auth-step auth-step-cyan">
                        <strong>Explora viajes disponibles</strong>
                        <span>Busca rutas por origen, destino, fecha y tipo de vehiculo para encontrar una opcion que encaje contigo.</span>
                    </div>
                    <div class="auth-step auth-step-blue">
                        <strong>Revisa el detalle del trayecto</strong>
                        <span>Consulta plazas, informacion del conductor y condiciones del viaje antes de tomar una decision.</span>
                    </div>
                    <div class="auth-step auth-step-sky">
                        <strong>Reserva y deja una nota</strong>
                        <span>Envias tu solicitud con observaciones utiles como equipaje, punto de encuentro o preferencias basicas.</span>
                    </div>
                    <a class="button button-primary auth-step-action" href="<?= app_url('/auth/login.php') ?>">Entrar como pasajero</a>
                </div>
            </article>

            <article class="card auth-explainer-card">
                <div class="card-body">
                    <h3>Proceso de conductor</h3>
                    <div class="auth-step auth-step-cyan">
                        <strong>Crea tu cuenta y completa tu perfil</strong>
                        <span>En pocos pasos dejas lista tu cuenta para publicar trayectos y recibir reservas.</span>
                    </div>
                    <div class="auth-step auth-step-blue">
                        <strong>Publica un viaje con sus plazas</strong>
                        <span>Define ruta, fecha, vehiculo y plazas disponibles para que otros usuarios puedan encontrarte facilmente.</span>
                    </div>
                    <div class="auth-step auth-step-turquoise">
                        <strong>Gestiona reservas y datos del trayecto</strong>
                        <span>Desde tu panel puedes revisar tus viajes, tu perfil y la informacion basica que ve cada pasajero.</span>
                    </div>
                    <a class="button button-primary auth-step-action" href="<?= app_url('/auth/registro.php') ?>">Empezar como conductor</a>
                </div>
            </article>
        </div>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
