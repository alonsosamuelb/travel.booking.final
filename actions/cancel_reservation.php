<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$reservationId = (int) ($_POST['reservation_id'] ?? 0);

$statement = db()->prepare(
    'UPDATE reservations
     SET status = "cancelled",
         payment_status = CASE WHEN payment_status = "paid" THEN "refunded" ELSE payment_status END,
         updated_at = NOW()
     WHERE id = :id AND user_id = :user_id AND status = "active"'
);
$statement->execute([
    'id' => $reservationId,
    'user_id' => current_user()['id'],
]);

set_flash('success', 'Reserva cancelada.');
redirect_to(app_url('/trips/mis-reservas.php'));
