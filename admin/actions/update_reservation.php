<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/admin/reservas.php'));
}

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$action = (string) ($_POST['action_name'] ?? '');

$statement = db()->prepare(
    'SELECT r.*, t.departure_at
     FROM reservations r
     INNER JOIN trips t ON t.id = r.trip_id
     WHERE r.id = :id
     LIMIT 1'
);
$statement->execute(['id' => $reservationId]);
$reservation = $statement->fetch();

if (!$reservation) {
    set_flash('error', 'No se encontro la reserva.');
    redirect_to(app_url('/admin/reservas.php'));
}

if ($action === 'cancel') {
    $update = db()->prepare(
        'UPDATE reservations
         SET status = "cancelled",
             payment_status = CASE WHEN payment_status = "paid" THEN "refunded" ELSE payment_status END,
             updated_at = NOW()
         WHERE id = :id
         LIMIT 1'
    );
    $update->execute(['id' => $reservationId]);
    set_flash('success', 'Reserva cancelada por administracion.');
    redirect_to(app_url('/admin/reservas.php'));
}

if ($action === 'delete') {
    if (strtotime((string) $reservation['departure_at']) <= time()) {
        set_flash('error', 'Solo puedes eliminar definitivamente reservas futuras.');
        redirect_to(app_url('/admin/reservas.php'));
    }

    $delete = db()->prepare('DELETE FROM reservations WHERE id = :id LIMIT 1');
    $delete->execute(['id' => $reservationId]);
    set_flash('success', 'Reserva eliminada definitivamente.');
    redirect_to(app_url('/admin/reservas.php'));
}

redirect_to(app_url('/admin/reservas.php'));
