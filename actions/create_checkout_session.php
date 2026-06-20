<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$reservation = find_user_reservation($reservationId, (int) current_user()['id']);

if (!$reservation) {
    set_flash('error', 'No hemos encontrado esa reserva para iniciar el pago.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

if (!reservation_can_start_payment($reservation)) {
    set_flash('error', 'Esta reserva ya no puede iniciar un pago online.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

if (!stripe_is_configured()) {
    set_flash('error', 'Stripe aun no esta configurado en este entorno.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

try {
    $session = stripe_create_checkout_session($reservation);
} catch (Throwable $exception) {
    set_flash('error', 'No se pudo iniciar el pago online: ' . $exception->getMessage());
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$checkoutUrl = (string) ($session['url'] ?? '');

if ($checkoutUrl === '') {
    set_flash('error', 'Stripe no devolvio una URL valida de pago.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

redirect_to($checkoutUrl);
