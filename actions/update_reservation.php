<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$reservationId = max(0, (int) ($_POST['reservation_id'] ?? 0));
$reservation = $reservationId > 0 ? find_user_reservation($reservationId, (int) current_user()['id']) : null;

if (!$reservation) {
    set_flash('error', 'No puedes editar esa reserva.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

if (!user_can_edit_reservation($reservation)) {
    set_flash('error', 'Solo puedes editar reservas activas antes de la salida.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$seatsReserved = max(1, (int) ($_POST['seats_reserved'] ?? 1));
$notes = trim((string) ($_POST['notes'] ?? ''));
$notes = mb_substr($notes, 0, 500);

$takenSeatsStatement = db()->prepare(
    'SELECT COALESCE(SUM(seats_reserved), 0)
     FROM reservations
     WHERE trip_id = :trip_id
       AND status = "active"
       AND id <> :reservation_id'
);
$takenSeatsStatement->execute([
    'trip_id' => (int) $reservation['trip_id'],
    'reservation_id' => $reservationId,
]);
$takenSeats = (int) $takenSeatsStatement->fetchColumn();
$availableSeats = max(0, (int) $reservation['available_seats'] - $takenSeats);

if ($seatsReserved > $availableSeats) {
    set_flash('error', 'No hay plazas suficientes para guardar ese cambio.');
    redirect_to(app_url('/trips/editar-reserva.php?id=' . $reservationId));
}

$statement = db()->prepare(
    'UPDATE reservations
     SET seats_reserved = :seats_reserved,
         notes = :notes,
         updated_at = NOW()
     WHERE id = :id
       AND user_id = :user_id
     LIMIT 1'
);
$statement->execute([
    'seats_reserved' => $seatsReserved,
    'notes' => $notes !== '' ? $notes : null,
    'id' => $reservationId,
    'user_id' => (int) current_user()['id'],
]);

set_flash('success', 'Reserva actualizada correctamente.');
redirect_to(app_url('/trips/mis-reservas.php'));
