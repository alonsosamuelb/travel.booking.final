<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/mis-viajes.php'));
}

$tripId = max(0, (int) ($_POST['trip_id'] ?? 0));
$trip = $tripId > 0 ? find_trip($tripId) : null;

if (!$trip || (int) ($trip['creator_user_id'] ?? 0) !== (int) current_user()['id']) {
    set_flash('error', 'No puedes editar ese viaje.');
    redirect_to(app_url('/trips/mis-viajes.php'));
}

$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$vehicle = trim((string) ($_POST['vehicle'] ?? ''));
$availableSeats = max(1, (int) ($_POST['available_seats'] ?? 1));
$imagePath = trim((string) ($_POST['image_path'] ?? ''));
$uploadedImage = $_FILES['image_file'] ?? null;
$previousImagePath = (string) ($trip['image_path'] ?? '');

if ($name === '' || $description === '' || $vehicle === '') {
    set_flash('error', 'Completa todos los campos obligatorios del viaje.');
    redirect_to(app_url('/trips/editar-viaje.php?id=' . $tripId));
}

if ($imagePath === '') {
    $imagePath = (string) ($trip['image_path'] ?? '');
}

if (is_array($uploadedImage) && (int) ($uploadedImage['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try {
        $imagePath = store_trip_uploaded_image($uploadedImage, 'trip-user');
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
        redirect_to(app_url('/trips/editar-viaje.php?id=' . $tripId));
    }
}

$nextImagePath = $imagePath !== '' ? $imagePath : null;

$statement = db()->prepare(
    'UPDATE trips
     SET name = :name,
         description = :description,
         vehicle = :vehicle,
         available_seats = :available_seats,
         image_path = :image_path,
         updated_at = NOW()
     WHERE id = :id AND creator_user_id = :creator_user_id'
);

$statement->execute([
    'name' => $name,
    'description' => $description,
    'vehicle' => $vehicle,
    'available_seats' => $availableSeats,
    'image_path' => $nextImagePath,
    'id' => $tripId,
    'creator_user_id' => (int) current_user()['id'],
]);

if ($previousImagePath !== (string) ($nextImagePath ?? '') && is_local_trip_uploaded_image($previousImagePath)) {
    delete_trip_uploaded_image($previousImagePath);
}

set_flash('success', 'Viaje actualizado correctamente.');
redirect_to(app_url('/trips/mis-viajes.php'));
