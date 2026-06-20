<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/auth/login.php'));
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    set_flash('error', 'Email y contrasena son obligatorios.');
    redirect_to(app_url('/auth/login.php?email=' . urlencode($email)));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo electronico valido.');
    redirect_to(app_url('/auth/login.php?email=' . urlencode($email)));
}

if (strlen($password) < 6) {
    set_flash('error', 'La contrasena debe tener al menos 6 caracteres.');
    redirect_to(app_url('/auth/login.php?email=' . urlencode($email)));
}

$statement = db()->prepare(
    'SELECT id, full_name, email, phone, role, password, deleted_at
     FROM users
     WHERE email = :email
     LIMIT 1'
);
$statement->execute(['email' => $email]);
$user = $statement->fetch();

if (!$user) {
    set_flash('error', 'No existe una cuenta con ese correo. Puedes registrarte ahora.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

if (!password_verify($password, (string) $user['password'])) {
    set_flash('error', 'Credenciales incorrectas.');
    redirect_to(app_url('/auth/login.php?email=' . urlencode($email)));
}

if (!empty($user['deleted_at'])) {
    set_flash('error', 'Tu cuenta esta inactiva. Puedes reactivarla a continuacion.');
    redirect_to(app_url('/auth/reactivar-cuenta.php?email=' . urlencode($email)));
}

unset($user['password']);
refresh_authenticated_session($user);

set_flash('success', 'Sesion iniciada correctamente.');
redirect_to(app_url('/trips/viajes.php'));
