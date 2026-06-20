<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Travel Booking PHP Simple';
$flash = get_flash();
$stylesPath = dirname(__DIR__) . '/assets/css/styles.css';
$stylesVersion = file_exists($stylesPath) ? (string) filemtime($stylesPath) : '1';
$logoPath = dirname(__DIR__) . '/assets/images/logo.png';
$logoVersion = file_exists($logoPath) ? (string) filemtime($logoPath) : '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Travel Booking </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url('/assets/css/styles.css') ?>?v=<?= e($stylesVersion) ?>">
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="site-header">
    <div class="shell nav-row">
        <a class="brand" href="<?= app_url('/index.php') ?>">
            <img class="brand-logo-image" src="<?= app_url('/assets/images/logo.png') ?>?v=<?= e($logoVersion) ?>" alt="Logo Travel Booking">
            <span class="brand-copy">
                <strong>Travel Booking </strong>
            
            </span>
        </a>
        <nav class="nav-links" aria-label="Principal">
            <?php if (is_admin()): ?>
                <a href="<?= app_url('/admin/admin.php') ?>"<?= page_is('admin.php') ? ' aria-current="page"' : '' ?>>Panel admin</a>
            <?php else: ?>
                <a href="<?= app_url('/trips/viajes.php?vehicle_group=car') ?>"<?= page_is('viajes.php') && vehicle_group_is('car') ? ' aria-current="page"' : '' ?>>Viajes coche</a>
                <a href="<?= app_url('/trips/viajes.php?vehicle_group=van') ?>"<?= page_is('viajes.php') && vehicle_group_is('van') ? ' aria-current="page"' : '' ?>>Viajes van</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= app_url('/trips/publicar-viaje.php') ?>"<?= page_is('publicar-viaje.php') ? ' aria-current="page"' : '' ?>>Publicar</a>
                    <a href="<?= app_url('/trips/mis-viajes.php') ?>"<?= page_is('mis-viajes.php') ? ' aria-current="page"' : '' ?>>Mis viajes</a>
                    <a href="<?= app_url('/trips/mis-reservas.php') ?>"<?= page_is('mis-reservas.php') ? ' aria-current="page"' : '' ?>>Mis reservas</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <a class="search-nav-link" href="<?= app_url('/trips/viajes.php') ?>" aria-label="Buscar viajes">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M10.5 4a6.5 6.5 0 1 1 0 13a6.5 6.5 0 0 1 0-13Zm0 2a4.5 4.5 0 1 0 0 9a4.5 4.5 0 0 0 0-9Zm8.9 11.5l-3.2-3.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </a>
            <?php if (is_logged_in()): ?>
                <a class="user-pill user-pill-link" href="<?= app_url('/account/perfil.php') ?>"<?= page_is('perfil.php') ? ' aria-current="page"' : '' ?>><?= e(current_user()['full_name']) ?></a>
                <form method="post" action="<?= app_url('/actions/logout.php') ?>">
                    <button class="button button-soft" type="submit">Cerrar sesion</button>
                </form>
            <?php else: ?>
                <details class="nav-user-menu">
                    <summary class="nav-user-toggle" aria-label="Abrir menu de acceso">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z" fill="currentColor"/>
                        </svg>
                    </summary>
                    <div class="nav-user-dropdown">
                        <a href="<?= app_url('/auth/login.php') ?>">Iniciar sesion</a>
                        <a href="<?= app_url('/auth/registro.php') ?>">Registrarse</a>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="page shell" id="contenido">
    <?php if ($flash): ?>
        <div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
