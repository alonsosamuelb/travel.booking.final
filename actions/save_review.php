<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$rating = max(1, min(5, (int) ($_POST['rating'] ?? 0)));
$comment = trim((string) ($_POST['comment'] ?? ''));
$comment = mb_substr($comment, 0, 600);
$context = review_context_for_user($reservationId, (int) current_user()['id']);

if (!$context) {
    set_flash('error', 'No se puede registrar una valoracion para esa reserva.');
    redirect_to(app_url('/trips/mis-reservas.php'));
}

$existing = user_review_for_pair(
    $reservationId,
    (int) current_user()['id'],
    (int) $context['reviewee_id']
);

if ($existing) {
    $statement = db()->prepare(
        'UPDATE trip_reviews
         SET rating = :rating,
             comment = :comment,
             updated_at = NOW()
         WHERE id = :id
         LIMIT 1'
    );
    $statement->execute([
        'rating' => $rating,
        'comment' => $comment !== '' ? $comment : null,
        'id' => (int) $existing['id'],
    ]);
    set_flash('success', 'Valoracion actualizada correctamente.');
} else {
    $statement = db()->prepare(
        'INSERT INTO trip_reviews (
            reservation_id, trip_id, reviewer_user_id, reviewee_user_id, reviewer_role,
            rating, comment, created_at, updated_at
         ) VALUES (
            :reservation_id, :trip_id, :reviewer_user_id, :reviewee_user_id, :reviewer_role,
            :rating, :comment, NOW(), NOW()
         )'
    );
    $statement->execute([
        'reservation_id' => $reservationId,
        'trip_id' => (int) $context['reservation']['trip_id'],
        'reviewer_user_id' => (int) current_user()['id'],
        'reviewee_user_id' => (int) $context['reviewee_id'],
        'reviewer_role' => (string) $context['reviewer_role'],
        'rating' => $rating,
        'comment' => $comment !== '' ? $comment : null,
    ]);
    set_flash('success', 'Valoracion enviada correctamente.');
}

if ((string) $context['mode'] === 'driver_to_passenger') {
    redirect_to(app_url('/trips/valorar-viajeros.php?trip_id=' . (int) $context['reservation']['trip_id']));
}

redirect_to(app_url('/trips/mis-reservas.php'));
