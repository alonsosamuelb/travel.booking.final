<?php

declare(strict_types=1);

function user_rating_summary(int $userId): array
{
    $statement = db()->prepare(
        'SELECT
            COUNT(*) AS reviews_count,
            COALESCE(AVG(rating), 0) AS reviews_average
         FROM trip_reviews
         WHERE reviewee_user_id = :user_id'
    );
    $statement->execute(['user_id' => $userId]);
    $summary = $statement->fetch();

    return [
        'count' => (int) ($summary['reviews_count'] ?? 0),
        'average' => round((float) ($summary['reviews_average'] ?? 0), 1),
    ];
}

function user_rating_breakdown(int $userId): array
{
    $statement = db()->prepare(
        'SELECT rating, COUNT(*) AS total
         FROM trip_reviews
         WHERE reviewee_user_id = :user_id
         GROUP BY rating'
    );
    $statement->execute(['user_id' => $userId]);

    $breakdown = [
        5 => 0,
        4 => 0,
        3 => 0,
        2 => 0,
        1 => 0,
    ];

    foreach ($statement->fetchAll() as $row) {
        $rating = (int) ($row['rating'] ?? 0);
        if (isset($breakdown[$rating])) {
            $breakdown[$rating] = (int) ($row['total'] ?? 0);
        }
    }

    return $breakdown;
}

function user_review_for_pair(int $reservationId, int $reviewerId, int $revieweeId): ?array
{
    $statement = db()->prepare(
        'SELECT *
         FROM trip_reviews
         WHERE reservation_id = :reservation_id
           AND reviewer_user_id = :reviewer_id
           AND reviewee_user_id = :reviewee_id
         LIMIT 1'
    );
    $statement->execute([
        'reservation_id' => $reservationId,
        'reviewer_id' => $reviewerId,
        'reviewee_id' => $revieweeId,
    ]);

    $review = $statement->fetch();
    return $review ?: null;
}

function review_context_for_user(int $reservationId, int $currentUserId): ?array
{
    sync_completed_reservations();

    $statement = db()->prepare(
        'SELECT
            r.*,
            passenger.full_name AS passenger_name,
            passenger.email AS passenger_email,
            driver.id AS driver_id,
            driver.full_name AS driver_name,
            driver.email AS driver_email,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at,
            t.creator_user_id
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         INNER JOIN users passenger ON passenger.id = r.user_id
         LEFT JOIN users driver ON driver.id = t.creator_user_id
         WHERE r.id = :reservation_id
           AND r.status = "completed"
         LIMIT 1'
    );
    $statement->execute(['reservation_id' => $reservationId]);
    $reservation = $statement->fetch();

    if (!$reservation) {
        return null;
    }

    if ((int) $reservation['user_id'] === $currentUserId) {
        $revieweeId = (int) ($reservation['driver_id'] ?? 0);
        if ($revieweeId <= 0 || $revieweeId === $currentUserId) {
            return null;
        }

        return [
            'reservation' => $reservation,
            'mode' => 'passenger_to_driver',
            'reviewee_id' => $revieweeId,
            'reviewee_name' => (string) ($reservation['driver_name'] ?? 'Conductor'),
            'reviewer_role' => 'passenger',
        ];
    }

    if ((int) ($reservation['creator_user_id'] ?? 0) === $currentUserId) {
        $revieweeId = (int) $reservation['user_id'];
        if ($revieweeId === $currentUserId) {
            return null;
        }

        return [
            'reservation' => $reservation,
            'mode' => 'driver_to_passenger',
            'reviewee_id' => $revieweeId,
            'reviewee_name' => (string) ($reservation['passenger_name'] ?? 'Pasajero'),
            'reviewer_role' => 'driver',
        ];
    }

    return null;
}

function owner_completed_trip_reviews(int $ownerId, int $tripId): array
{
    sync_completed_reservations();

    $statement = db()->prepare(
        'SELECT
            r.id,
            r.trip_id,
            r.seats_reserved,
            r.payment_status,
            passenger.id AS passenger_id,
            passenger.full_name AS passenger_name,
            passenger.email AS passenger_email,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         INNER JOIN users passenger ON passenger.id = r.user_id
         WHERE t.id = :trip_id
           AND t.creator_user_id = :owner_id
           AND r.status = "completed"
         ORDER BY r.created_at ASC'
    );
    $statement->execute([
        'trip_id' => $tripId,
        'owner_id' => $ownerId,
    ]);

    $rows = $statement->fetchAll();

    foreach ($rows as &$row) {
        $row['existing_review'] = user_review_for_pair((int) $row['id'], $ownerId, (int) $row['passenger_id']);
    }
    unset($row);

    return $rows;
}

function public_user_profile(int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT id, full_name, email, phone, role, created_at
         FROM users
         WHERE id = :user_id
           AND deleted_at IS NULL
         LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $user = $statement->fetch();

    return $user ?: null;
}

function public_user_trip_cards(int $userId, int $limit = 6): array
{
    sync_completed_reservations();

    $limit = max(1, min(24, $limit));
    $statement = db()->prepare(
        'SELECT
            t.*,
            COALESCE(SUM(CASE WHEN r.status = "active" THEN r.seats_reserved ELSE 0 END), 0) AS reserved_seats
         FROM trips t
         LEFT JOIN reservations r ON r.trip_id = t.id
         WHERE t.creator_user_id = :user_id
           AND t.status = "published"
         GROUP BY t.id
         ORDER BY t.departure_at DESC
         LIMIT :limit'
    );
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

function public_user_received_reviews(int $userId, int $limit = 10): array
{
    $limit = max(1, min(30, $limit));
    $statement = db()->prepare(
        'SELECT
            tr.*,
            reviewer.id AS reviewer_id,
            reviewer.full_name AS reviewer_name,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at
         FROM trip_reviews tr
         INNER JOIN users reviewer ON reviewer.id = tr.reviewer_user_id
         INNER JOIN trips t ON t.id = tr.trip_id
         WHERE tr.reviewee_user_id = :user_id
         ORDER BY tr.updated_at DESC, tr.created_at DESC
         LIMIT :limit'
    );
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}
