<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/admin/viajes.php'));
}

$tripId = (int) ($_POST['trip_id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$origin = trim((string) ($_POST['origin'] ?? ''));
$destination = trim((string) ($_POST['destination'] ?? ''));
$departureAt = str_replace('T', ' ', trim((string) ($_POST['departure_at'] ?? '')));
$vehicle = trim((string) ($_POST['vehicle'] ?? ''));
$availableSeats = max(1, (int) ($_POST['available_seats'] ?? 1));
$pricePerSeat = parse_money_input((string) ($_POST['price_per_seat'] ?? '0'));
$status = (string) ($_POST['status'] ?? 'published');
$creatorUserId = (int) ($_POST['creator_user_id'] ?? 0);
$imagePath = trim((string) ($_POST['image_path'] ?? ''));
$uploadedImage = $_FILES['image_file'] ?? null;

if ($name === '' || $description === '' || $origin === '' || $destination === '' || $departureAt === '' || $vehicle === '' || $creatorUserId <= 0) {
    set_flash('error', 'Completa todos los campos obligatorios del viaje.');
    redirect_to(app_url($tripId > 0 ? '/admin/viaje-form.php?id=' . $tripId : '/admin/viaje-form.php'));
}

if ($pricePerSeat <= 0) {
    set_flash('error', 'Define un precio por plaza valido para el viaje.');
    redirect_to(app_url($tripId > 0 ? '/admin/viaje-form.php?id=' . $tripId : '/admin/viaje-form.php'));
}

if (!in_array($status, ['published', 'draft'], true)) {
    $status = 'draft';
}

$creator = admin_find_user($creatorUserId);

if (!$creator) {
    set_flash('error', 'No se encontro el usuario responsable del viaje.');
    redirect_to(app_url($tripId > 0 ? '/admin/viaje-form.php?id=' . $tripId : '/admin/viaje-form.php'));
}

if (is_array($uploadedImage) && (int) ($uploadedImage['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    try {
        $imagePath = store_trip_uploaded_image($uploadedImage, 'trip-admin');
    } catch (RuntimeException $exception) {
        set_flash('error', $exception->getMessage());
        redirect_to(app_url($tripId > 0 ? '/admin/viaje-form.php?id=' . $tripId : '/admin/viaje-form.php'));
    }
}

if ($tripId > 0) {
    $trip = find_trip($tripId);

    if (!$trip) {
        set_flash('error', 'No se encontro el viaje.');
        redirect_to(app_url('/admin/viajes.php'));
    }

    $previousImagePath = (string) ($trip['image_path'] ?? '');
    $nextImagePath = $imagePath !== '' ? $imagePath : null;

    $statement = db()->prepare(
        'UPDATE trips
         SET name = :name,
             description = :description,
             origin = :origin,
             destination = :destination,
             departure_at = :departure_at,
             vehicle = :vehicle,
             available_seats = :available_seats,
             price_per_seat = :price_per_seat,
             image_path = :image_path,
             status = :status,
             creator_user_id = :creator_user_id,
             updated_at = NOW()
         WHERE id = :id'
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
        'image_path' => $nextImagePath,
        'status' => $status,
        'creator_user_id' => $creatorUserId,
        'id' => $tripId,
    ]);

    if ($previousImagePath !== (string) ($nextImagePath ?? '') && is_local_trip_uploaded_image($previousImagePath)) {
        delete_trip_uploaded_image($previousImagePath);
    }

    set_flash('success', 'Viaje actualizado correctamente.');
    redirect_to(app_url('/admin/viajes.php'));
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
    'creator_user_id' => $creatorUserId,
]);

set_flash('success', 'Viaje creado correctamente.');
redirect_to(app_url('/admin/viajes.php'));
