<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/auth/reactivar-cuenta.php'));
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    set_flash('error', 'Correo y contrasena son obligatorios.');
    redirect_to(app_url('/auth/reactivar-cuenta.php?email=' . urlencode($email)));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo electronico valido.');
    redirect_to(app_url('/auth/reactivar-cuenta.php?email=' . urlencode($email)));
}

$statement = db()->prepare(
    'SELECT id, full_name, email, phone, role, password, deleted_at
     FROM users
     WHERE email = :email
     LIMIT 1'
);
$statement->execute(['email' => $email]);
$user = $statement->fetch();

if (!$user || !$user['deleted_at']) {
    set_flash('error', 'No hemos encontrado una cuenta inactiva con ese correo.');
    redirect_to(app_url('/auth/reactivar-cuenta.php?email=' . urlencode($email)));
}

if (!password_verify($password, (string) $user['password'])) {
    set_flash('error', 'Las credenciales no coinciden con la cuenta inactiva.');
    redirect_to(app_url('/auth/reactivar-cuenta.php?email=' . urlencode($email)));
}

$update = db()->prepare('UPDATE users SET deleted_at = NULL, updated_at = NOW() WHERE id = :id LIMIT 1');
$update->execute(['id' => $user['id']]);

unset($user['password'], $user['deleted_at']);
$_SESSION['user'] = $user;

set_flash('success', 'Tu cuenta ha sido reactivada correctamente.');
redirect_to(app_url('/trips/viajes.php'));
