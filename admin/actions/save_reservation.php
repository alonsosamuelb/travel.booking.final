<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/admin/reservas.php'));
}

sync_completed_reservations();

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$userId = (int) ($_POST['user_id'] ?? 0);
$tripId = (int) ($_POST['trip_id'] ?? 0);
$seatsReserved = max(1, (int) ($_POST['seats_reserved'] ?? 1));
$pricePerSeat = parse_money_input((string) ($_POST['price_per_seat'] ?? '0'));
$status = (string) ($_POST['status'] ?? 'active');
$paymentStatus = (string) ($_POST['payment_status'] ?? 'pending');
$travelRole = ($_POST['travel_role'] ?? 'passenger') === 'driver' ? 'driver' : 'passenger';
$notes = trim((string) ($_POST['notes'] ?? ''));
$notes = mb_substr($notes, 0, 500);

if ($userId <= 0 || $tripId <= 0) {
    set_flash('error', 'Selecciona usuario y viaje.');
    redirect_to(app_url($reservationId > 0 ? '/admin/reserva-form.php?id=' . $reservationId : '/admin/reserva-form.php'));
}

if (!in_array($status, ['active', 'cancelled', 'completed'], true)) {
    $status = 'active';
}

if (!in_array($paymentStatus, ['pending', 'paid', 'refunded'], true)) {
    $paymentStatus = 'pending';
}

$user = admin_find_user($userId);
$trip = find_trip($tripId);

if (!$user || !$trip) {
    set_flash('error', 'El usuario o el viaje seleccionado ya no existe.');
    redirect_to(app_url($reservationId > 0 ? '/admin/reserva-form.php?id=' . $reservationId : '/admin/reserva-form.php'));
}

if ($pricePerSeat <= 0) {
    $pricePerSeat = round((float) ($trip['price_per_seat'] ?? 0), 2);
}

if ($pricePerSeat <= 0) {
    set_flash('error', 'El viaje seleccionado necesita un precio por plaza valido.');
    redirect_to(app_url($reservationId > 0 ? '/admin/reserva-form.php?id=' . $reservationId : '/admin/reserva-form.php'));
}

$tripAlreadyPassed = strtotime((string) ($trip['departure_at'] ?? '')) < time();

if ($tripAlreadyPassed && $status === 'active') {
    $status = 'completed';
}

$totalPrice = platform_booking_fee();

$takenSeatsStatement = db()->prepare(
    'SELECT COALESCE(SUM(seats_reserved), 0)
     FROM reservations
     WHERE trip_id = :trip_id
       AND status = "active"
       AND id <> :reservation_id'
);
$takenSeatsStatement->execute([
    'trip_id' => $tripId,
    'reservation_id' => $reservationId,
]);
$takenSeats = (int) $takenSeatsStatement->fetchColumn();
$availableSeatsForEdit = max(0, (int) $trip['available_seats'] - $takenSeats);

if ($status === 'active' && $seatsReserved > $availableSeatsForEdit) {
    set_flash('error', 'No hay plazas suficientes para dejar esta reserva activa con ese numero de asientos.');
    redirect_to(app_url($reservationId > 0 ? '/admin/reserva-form.php?id=' . $reservationId : '/admin/reserva-form.php'));
}

if ($reservationId > 0) {
    $reservation = admin_find_reservation($reservationId);

    if (!$reservation) {
        set_flash('error', 'No se encontro la reserva.');
        redirect_to(app_url('/admin/reservas.php'));
    }

    $statement = db()->prepare(
        'UPDATE reservations
         SET user_id = :user_id,
             trip_id = :trip_id,
             seats_reserved = :seats_reserved,
             price_per_seat = :price_per_seat,
             total_price = :total_price,
             travel_role = :travel_role,
             notes = :notes,
             status = :status,
             payment_status = :payment_status,
             paid_at = CASE
                 WHEN :payment_status_paid = "paid" THEN COALESCE(paid_at, NOW())
                 WHEN :payment_status_pending = "pending" THEN NULL
                 ELSE paid_at
             END,
             updated_at = NOW()
         WHERE id = :id
         LIMIT 1'
    );
    $statement->execute([
        'user_id' => $userId,
        'trip_id' => $tripId,
        'seats_reserved' => $seatsReserved,
        'price_per_seat' => $pricePerSeat,
        'total_price' => $totalPrice,
        'travel_role' => $travelRole,
        'notes' => $notes !== '' ? $notes : null,
        'status' => $status,
        'payment_status' => $paymentStatus,
        'payment_status_paid' => $paymentStatus,
        'payment_status_pending' => $paymentStatus,
        'id' => $reservationId,
    ]);

    set_flash('success', 'Reserva actualizada correctamente.');
    redirect_to(app_url('/admin/reservas.php'));
}

$statement = db()->prepare(
    'INSERT INTO reservations (
        user_id, trip_id, reservation_date, seats_reserved, price_per_seat, total_price, travel_role, notes, status, payment_status, paid_at, created_at, updated_at
     ) VALUES (
        :user_id, :trip_id, NOW(), :seats_reserved, :price_per_seat, :total_price, :travel_role, :notes, :status, :payment_status, :paid_at, NOW(), NOW()
     )'
);
$statement->execute([
    'user_id' => $userId,
    'trip_id' => $tripId,
    'seats_reserved' => $seatsReserved,
    'price_per_seat' => $pricePerSeat,
    'total_price' => $totalPrice,
    'travel_role' => $travelRole,
    'notes' => $notes !== '' ? $notes : null,
    'status' => $status,
    'payment_status' => $paymentStatus,
    'paid_at' => $paymentStatus === 'paid' ? date('Y-m-d H:i:s') : null,
]);

set_flash('success', 'Reserva creada correctamente.');
redirect_to(app_url('/admin/reservas.php'));
