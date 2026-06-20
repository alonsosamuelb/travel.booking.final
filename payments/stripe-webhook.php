<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$payload = file_get_contents('php://input') ?: '';
$signature = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
$secret = stripe_config()['webhook_secret'];
$event = stripe_parse_webhook_event($payload, $signature, $secret);

if (!$event) {
    http_response_code(400);
    echo 'Invalid signature';
    exit;
}

$type = (string) ($event['type'] ?? '');
$object = $event['data']['object'] ?? null;

if (!is_array($object)) {
    http_response_code(200);
    echo 'Ignored';
    exit;
}

if ($type === 'checkout.session.completed') {
    sync_reservation_payment_from_checkout_session($object);
}

http_response_code(200);
echo 'OK';
