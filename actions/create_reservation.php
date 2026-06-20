<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/viajes.php'));
}

sync_completed_reservations();

$tripId = (int) ($_POST['trip_id'] ?? 0);
$seatsReserved = max(1, (int) ($_POST['seats_reserved'] ?? 1));
$travelRole = ($_POST['travel_role'] ?? 'passenger') === 'driver' ? 'driver' : 'passenger';
$notes = trim((string) ($_POST['notes'] ?? ''));
$notes = mb_substr($notes, 0, 500);
$userId = (int) current_user()['id'];
$adminMode = is_admin();
$dailyReservationLimit = 2;
$weeklySeatsLimit = 4;

$trip = find_trip($tripId);

if (!$trip || $trip['status'] !== 'published') {
    set_flash('error', 'El viaje ya no esta disponible.');
    redirect_to(app_url('/trips/viajes.php'));
}

if (strtotime((string) $trip['departure_at']) <= time()) {
    set_flash('error', 'Solo puedes reservar viajes futuros.');
    redirect_to(app_url('/trips/viajes.php'));
}

if ((int) ($trip['creator_user_id'] ?? 0) === $userId) {
    set_flash('error', 'No puedes reservar tu propio viaje.');
    redirect_to(app_url('/trips/viajes.php'));
}

$pricePerSeat = round((float) ($trip['price_per_seat'] ?? 0), 2);

if ($pricePerSeat <= 0) {
    set_flash('error', 'Este viaje aun no tiene un precio por plaza definido.');
    redirect_to(app_url('/trips/viaje.php?id=' . $tripId));
}

$duplicate = db()->prepare(
    'SELECT id FROM reservations WHERE user_id = :user_id AND trip_id = :trip_id AND status = "active" LIMIT 1'
);
$duplicate->execute([
    'user_id' => $userId,
    'trip_id' => $tripId,
]);

if ($duplicate->fetch()) {
    set_flash('error', 'Ya tienes una reserva activa para este viaje.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

if (!$adminMode) {
    $sameTime = db()->prepare(
        'SELECT r.id
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.user_id = :user_id
           AND r.status = "active"
           AND t.departure_at = :departure_at
         LIMIT 1'
    );
    $sameTime->execute([
        'user_id' => $userId,
        'departure_at' => $trip['departure_at'],
    ]);

    if ($sameTime->fetch()) {
        set_flash('error', 'Ya tienes otra reserva en la misma fecha y hora.');
        redirect_to(app_url('/trips/mis-reservas.php'));
    }

    $sameDay = db()->prepare(
        'SELECT COUNT(*)
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.user_id = :user_id
           AND r.status = "active"
           AND r.payment_status = "paid"
           AND DATE(t.departure_at) = DATE(:departure_at)'
    );
    $sameDay->execute([
        'user_id' => $userId,
        'departure_at' => $trip['departure_at'],
    ]);

    if ((int) $sameDay->fetchColumn() >= $dailyReservationLimit) {
        set_flash('error', 'Has alcanzado el limite de reservas activas para ese dia.');
        redirect_to(app_url('/trips/mis-reservas.php'));
    }

    $vehicleGroup = vehicle_group_from_vehicle((string) $trip['vehicle']);
    $sameVehicleWeek = db()->prepare(
        'SELECT COUNT(*)
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.user_id = :user_id
           AND r.status = "active"
           AND r.payment_status = "paid"
           AND YEARWEEK(t.departure_at, 3) = YEARWEEK(:departure_at, 3)
           AND ' . ($vehicleGroup === 'van'
                ? 'LOWER(t.vehicle) LIKE "%van%"'
                : 'LOWER(t.vehicle) NOT LIKE "%van%"')
    );
    $sameVehicleWeek->execute([
        'user_id' => $userId,
        'departure_at' => $trip['departure_at'],
    ]);

    if ((int) $sameVehicleWeek->fetchColumn() >= 1) {
        set_flash('error', 'No puedes reservar dos viajes del mismo tipo en la misma semana.');
        redirect_to(app_url('/trips/mis-reservas.php'));
    }

    $weeklySeats = db()->prepare(
        'SELECT COALESCE(SUM(r.seats_reserved), 0)
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.user_id = :user_id
           AND r.status = "active"
           AND r.payment_status = "paid"
           AND YEARWEEK(t.departure_at, 3) = YEARWEEK(:departure_at, 3)'
    );
    $weeklySeats->execute([
        'user_id' => $userId,
        'departure_at' => $trip['departure_at'],
    ]);

    if ((int) $weeklySeats->fetchColumn() + $seatsReserved > $weeklySeatsLimit) {
        set_flash('error', 'Superarias el limite semanal de plazas reservadas.');
        redirect_to(app_url('/trips/mis-reservas.php'));
    }

    $activeCount = db()->prepare(
        'SELECT COUNT(*)
         FROM reservations
         WHERE user_id = :user_id
           AND status = "active"
           AND payment_status = "paid"'
    );
    $activeCount->execute(['user_id' => $userId]);

    if ((int) $activeCount->fetchColumn() >= 3) {
        set_flash('error', 'Has alcanzado el limite de reservas activas.');
        redirect_to(app_url('/trips/mis-reservas.php'));
    }
}

if ($seatsReserved > remaining_seats($trip)) {
    set_flash('error', 'No hay suficientes plazas disponibles.');
    redirect_to(app_url('/trips/viajes.php'));
}

$totalPrice = platform_booking_fee();

$insert = db()->prepare(
    'INSERT INTO reservations (
        user_id, trip_id, reservation_date, seats_reserved, price_per_seat, total_price, travel_role, notes, status, payment_status, created_at, updated_at
     ) VALUES (
        :user_id, :trip_id, NOW(), :seats_reserved, :price_per_seat, :total_price, :travel_role, :notes, "active", "pending", NOW(), NOW()
     )'
);
$insert->execute([
    'user_id' => $userId,
    'trip_id' => $tripId,
    'seats_reserved' => $seatsReserved,
    'price_per_seat' => $pricePerSeat,
    'total_price' => $totalPrice,
    'travel_role' => $travelRole,
    'notes' => $notes !== '' ? $notes : null,
]);

$reservationId = (int) db()->lastInsertId();

set_flash('success', 'Reserva creada correctamente. Ahora puedes abonar la cuota de reserva de ' . format_money($totalPrice) . ' y el resto se paga al conductor por ' . off_platform_payment_methods_label() . '.');
redirect_to(app_url('/payments/pago.php?reservation_id=' . $reservationId));
