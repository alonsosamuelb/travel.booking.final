<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/pages/contacto.php'));
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    set_flash('error', 'Completa todos los campos del formulario de contacto.');
    redirect_to(app_url('/pages/contacto.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo electronico valido para contactar contigo.');
    redirect_to(app_url('/pages/contacto.php'));
}

if (mb_strlen($message) < 10) {
    set_flash('error', 'El mensaje debe tener al menos 10 caracteres.');
    redirect_to(app_url('/pages/contacto.php'));
}

set_flash('success', 'Tu mensaje se ha enviado correctamente. Te responderemos lo antes posible.');
redirect_to(app_url('/pages/contacto.php'));
