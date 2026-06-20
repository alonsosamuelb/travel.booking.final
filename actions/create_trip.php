<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/publicar-viaje.php'));
}

$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$origin = trim((string) ($_POST['origin'] ?? ''));
$destination = trim((string) ($_POST['destination'] ?? ''));
$departureAt = trim((string) ($_POST['departure_at'] ?? ''));
$vehicle = trim((string) ($_POST['vehicle'] ?? ''));
$availableSeats = max(1, (int) ($_POST['available_seats'] ?? 1));
$pricePerSeat = parse_money_input((string) ($_POST['price_per_seat'] ?? '0'));
$status = 'published';
$imagePath = trim((string) ($_POST['image_path'] ?? ''));
$uploadedImage = $_FILES['image_file'] ?? null;

if ($name === '' || $description === '' || $origin === '' || $destination === '' || $departureAt === '' || $vehicle === '') {
    set_flash('error', 'Completa todos los campos obligatorios del viaje.');
    redirect_to(app_url('/trips/publicar-viaje.php'));
}

if ($pricePerSeat <= 0) {
    set_flash('error', 'Define un precio por plaza valido para publicar el viaje.');
    redirect_to(app_url('/trips/publicar-viaje.php'));
}

$departureAt = str_replace('T', ' ', $departureAt);

if (is_array($uploadedImage) && (int) ($uploadedImage['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try {
        $imagePath = store_trip_uploaded_image($uploadedImage, 'trip');
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
        redirect_to(app_url('/trips/publicar-viaje.php'));
    }
}

$statement = db()->prepare(
    'INSERT INTO trips (
        name, description, origin, destination, departure_at, vehicle,
        available_seats, price_per_seat, image_path, status, creator_user_id, created_at, updated_at
     ) VALUES (
        :name, :description, :origin, :destination, :departure_at, :vehicle,
        :available_seats, :price_per_seat, :image_path, :status, :creator_user_id, NOW(), NOW()
     )'
);
$statement->execute([
    'name' => $name,
    'description' => $description,
    'origin' => $origin,
    'destination' => $destination,
    'departure_at' => $departureAt,
    'vehicle' => $vehicle,
    'available_seats' => $availableSeats,
    'price_per_seat' => $pricePerSeat,
    'image_path' => $imagePath !== '' ? $imagePath : null,
    'status' => $status,
    'creator_user_id' => current_user()['id'],
]);

set_flash('success', 'Viaje guardado correctamente.');
redirect_to(app_url('/trips/mis-viajes.php'));
