<?php

declare(strict_types=1);

use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Webhook;

function stripe_local_config(): array
{
    static $config;

    if ($config !== null) {
        return $config;
    }

    $path = dirname(__DIR__) . '/config/stripe.local.php';

    if (!file_exists($path)) {
        $config = [];
        return $config;
    }

    $loaded = require $path;
    $config = is_array($loaded) ? $loaded : [];

    return $config;
}

function stripe_config(): array
{
    $local = stripe_local_config();

    return [
        'secret_key' => trim((string) ($local['secret_key'] ?? getenv('STRIPE_SECRET_KEY') ?: '')),
        'publishable_key' => trim((string) ($local['publishable_key'] ?? getenv('STRIPE_PUBLISHABLE_KEY') ?: '')),
        'webhook_secret' => trim((string) ($local['webhook_secret'] ?? getenv('STRIPE_WEBHOOK_SECRET') ?: '')),
        'currency' => strtolower(trim((string) ($local['currency'] ?? getenv('STRIPE_CURRENCY') ?: 'eur'))),
    ];
}

function stripe_sdk_available(): bool
{
    return class_exists(StripeClient::class) && class_exists(Session::class) && class_exists(Webhook::class);
}

function stripe_is_configured(): bool
{
    $config = stripe_config();

    return $config['secret_key'] !== '' && stripe_sdk_available();
}

function stripe_client(): StripeClient
{
    if (!stripe_sdk_available()) {
        throw new RuntimeException('El SDK oficial de Stripe no esta cargado. Revisa vendor/autoload.php.');
    }

    $config = stripe_config();

    if ($config['secret_key'] === '') {
        throw new RuntimeException('Stripe no esta configurado todavia.');
    }

    return new StripeClient($config['secret_key']);
}

function payment_status_badge_class(string $status): string
{
    return match ($status) {
        'paid' => 'status-published',
        'refunded' => 'status-cancelled',
        default => 'status-draft',
    };
}

function reservation_can_start_payment(array $reservation): bool
{
    return (string) ($reservation['status'] ?? '') === 'active'
        && (string) ($reservation['payment_status'] ?? 'pending') === 'pending'
        && (float) ($reservation['total_price'] ?? 0) > 0
        && strtotime((string) ($reservation['departure_at'] ?? '')) > time();
}

function find_reservation_by_checkout_session(string $sessionId): ?array
{
    $statement = db()->prepare(
        'SELECT r.*, t.departure_at, t.name AS trip_name
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.stripe_checkout_session_id = :session_id
         LIMIT 1'
    );
    $statement->execute(['session_id' => $sessionId]);
    $reservation = $statement->fetch();

    return $reservation ?: null;
}

function stripe_create_checkout_session(array $reservation): array
{
    $reservationId = (int) $reservation['id'];
    $tripName = 'Cuota de reserva Travel Booking';
    $route = trim((string) ($reservation['origin'] ?? '') . ' → ' . (string) ($reservation['destination'] ?? ''));

    try {
        $session = stripe_client()->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => app_absolute_url('/payments/pago-exito.php?session_id={CHECKOUT_SESSION_ID}&reservation_id=' . $reservationId),
            'cancel_url' => app_absolute_url('/payments/pago-cancelado.php?reservation_id=' . $reservationId),
            'client_reference_id' => (string) $reservationId,
            'customer_email' => (string) ($reservation['email'] ?? ''),
            'metadata' => [
                'reservation_id' => (string) $reservationId,
                'trip_id' => (string) ($reservation['trip_id'] ?? ''),
                'user_id' => (string) ($reservation['user_id'] ?? ''),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'reservation_id' => (string) $reservationId,
                    'trip_id' => (string) ($reservation['trip_id'] ?? ''),
                    'user_id' => (string) ($reservation['user_id'] ?? ''),
                ],
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => stripe_config()['currency'],
                    'product_data' => [
                        'name' => $tripName,
                        'description' => 'Formalizacion de la reserva · ' . $route . ' · ' . format_datetime((string) ($reservation['departure_at'] ?? '')),
                    ],
                    'unit_amount' => (int) round(platform_booking_fee() * 100),
                ],
                'quantity' => 1,
            ]],
        ]);
    } catch (ApiErrorException $exception) {
        throw new RuntimeException($exception->getMessage(), 0, $exception);
    }

    if (!empty($session->id)) {
        $statement = db()->prepare(
            'UPDATE reservations
             SET stripe_checkout_session_id = :session_id,
                 updated_at = NOW()
             WHERE id = :reservation_id
             LIMIT 1'
        );
        $statement->execute([
            'session_id' => (string) $session->id,
            'reservation_id' => $reservationId,
        ]);
    }

    return $session->toArray();
}

function stripe_retrieve_checkout_session(string $sessionId): array
{
    try {
        $session = stripe_client()->checkout->sessions->retrieve($sessionId, [
            'expand' => ['payment_intent'],
        ]);
    } catch (ApiErrorException $exception) {
        throw new RuntimeException($exception->getMessage(), 0, $exception);
    }

    return $session->toArray();
}

function stripe_verify_webhook_signature(string $payload, string $signatureHeader, string $secret): bool
{
    if ($payload === '' || $signatureHeader === '' || $secret === '' || !stripe_sdk_available()) {
        return false;
    }

    try {
        Webhook::constructEvent($payload, $signatureHeader, $secret);
        return true;
    } catch (UnexpectedValueException|SignatureVerificationException $exception) {
        return false;
    }
}

function stripe_parse_webhook_event(string $payload, string $signatureHeader, string $secret): ?array
{
    if ($payload === '' || $signatureHeader === '' || $secret === '' || !stripe_sdk_available()) {
        return null;
    }

    try {
        $event = Webhook::constructEvent($payload, $signatureHeader, $secret);
        return $event->toArray();
    } catch (UnexpectedValueException|SignatureVerificationException $exception) {
        return null;
    }
}

function mark_reservation_paid(int $reservationId, ?string $checkoutSessionId = null, ?string $paymentIntentId = null): void
{
    $statement = db()->prepare(
        'UPDATE reservations
         SET payment_status = "paid",
             stripe_checkout_session_id = COALESCE(:checkout_session_id, stripe_checkout_session_id),
             stripe_payment_intent_id = COALESCE(:payment_intent_id, stripe_payment_intent_id),
             paid_at = COALESCE(paid_at, NOW()),
             updated_at = NOW()
         WHERE id = :reservation_id
         LIMIT 1'
    );
    $statement->execute([
        'checkout_session_id' => $checkoutSessionId,
        'payment_intent_id' => $paymentIntentId,
        'reservation_id' => $reservationId,
    ]);
}

function sync_reservation_payment_from_checkout_session(array $session): ?int
{
    $reservationId = (int) ($session['client_reference_id'] ?? $session['metadata']['reservation_id'] ?? 0);

    if ($reservationId <= 0) {
        return null;
    }

    $paymentStatus = (string) ($session['payment_status'] ?? '');
    $checkoutSessionId = !empty($session['id']) ? (string) $session['id'] : null;
    $paymentIntent = $session['payment_intent'] ?? null;
    $paymentIntentId = is_array($paymentIntent)
        ? (string) ($paymentIntent['id'] ?? '')
        : (is_string($paymentIntent) ? $paymentIntent : null);

    if ($paymentStatus === 'paid') {
        mark_reservation_paid($reservationId, $checkoutSessionId, $paymentIntentId ?: null);
        return $reservationId;
    }

    if ($checkoutSessionId) {
        $statement = db()->prepare(
            'UPDATE reservations
             SET stripe_checkout_session_id = COALESCE(:session_id, stripe_checkout_session_id),
                 stripe_payment_intent_id = COALESCE(:payment_intent_id, stripe_payment_intent_id),
                 updated_at = NOW()
             WHERE id = :reservation_id
             LIMIT 1'
        );
        $statement->execute([
            'session_id' => $checkoutSessionId,
            'payment_intent_id' => $paymentIntentId,
            'reservation_id' => $reservationId,
        ]);
    }

    return $reservationId;
}
