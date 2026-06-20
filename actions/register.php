<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/auth/registro.php'));
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($fullName === '' || $email === '' || $password === '') {
    set_flash('error', 'Nombre, email y contrasena son obligatorios.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

if (mb_strlen($fullName) < 3) {
    set_flash('error', 'El nombre debe tener al menos 3 caracteres.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo electronico valido.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

if (strlen($password) < 6) {
    set_flash('error', 'La contrasena debe tener al menos 6 caracteres.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

$existing = db()->prepare('SELECT id, deleted_at FROM users WHERE email = :email LIMIT 1');
$existing->execute(['email' => $email]);

$existingUser = $existing->fetch();

if ($existingUser) {
    if (!empty($existingUser['deleted_at'])) {
        set_flash('error', 'Ya existe una cuenta inactiva con ese correo. Puedes reactivarla.');
        redirect_to(app_url('/auth/reactivar-cuenta.php?email=' . urlencode($email)));
    }

    set_flash('error', 'Ya existe una cuenta con ese correo.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

$statement = db()->prepare(
    'INSERT INTO users (full_name, email, password, phone, role, deleted_at, created_at, updated_at)
     VALUES (:full_name, :email, :password, :phone, "user", NULL, NOW(), NOW())'
);
$statement->execute([
    'full_name' => $fullName,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'phone' => $phone !== '' ? $phone : null,
]);

$userId = (int) db()->lastInsertId();
$user = db()->prepare('SELECT id, full_name, email, phone, role FROM users WHERE id = :id LIMIT 1');
$user->execute(['id' => $userId]);
$_SESSION['user'] = $user->fetch();

set_flash('success', 'Cuenta creada correctamente.');
redirect_to(app_url('/trips/publicar-viaje.php'));
