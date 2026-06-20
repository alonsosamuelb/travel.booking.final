<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/mis-viajes.php'));
}

$tripId = (int) ($_POST['trip_id'] ?? 0);
$action = (string) ($_POST['action_name'] ?? '');

$statement = db()->prepare('SELECT * FROM trips WHERE id = :id AND creator_user_id = :user_id LIMIT 1');
$statement->execute([
    'id' => $tripId,
    'user_id' => current_user()['id'],
]);
$trip = $statement->fetch();

if (!$trip) {
    set_flash('error', 'No se encontro el viaje.');
    redirect_to(app_url('/trips/mis-viajes.php'));
}

if ($action === 'toggle') {
    $newStatus = $trip['status'] === 'published' ? 'draft' : 'published';
    $update = db()->prepare('UPDATE trips SET status = :status, updated_at = NOW() WHERE id = :id');
    $update->execute(['status' => $newStatus, 'id' => $tripId]);
    set_flash('success', 'Estado del viaje actualizado.');
    redirect_to(app_url('/trips/mis-viajes.php'));
}

if ($action === 'delete') {
    $activeReservations = db()->prepare('SELECT COUNT(*) FROM reservations WHERE trip_id = :trip_id AND status = "active"');
    $activeReservations->execute(['trip_id' => $tripId]);

    if ((int) $activeReservations->fetchColumn() > 0) {
        set_flash('error', 'No puedes eliminar un viaje con reservas activas.');
        redirect_to(app_url('/trips/mis-viajes.php'));
    }

    $delete = db()->prepare('DELETE FROM trips WHERE id = :id');
    $delete->execute(['id' => $tripId]);
    set_flash('success', 'Viaje eliminado.');
}

redirect_to(app_url('/trips/mis-viajes.php'));
