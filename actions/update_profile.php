<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/account/perfil.php'));
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$userId = (int) current_user()['id'];

if ($fullName === '' || $email === '') {
    set_flash('error', 'Nombre y correo electronico son obligatorios.');
    redirect_to(app_url('/account/perfil.php'));
}

if (mb_strlen($fullName) < 3) {
    set_flash('error', 'El nombre debe tener al menos 3 caracteres.');
    redirect_to(app_url('/account/perfil.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo electronico valido.');
    redirect_to(app_url('/account/perfil.php'));
}

$existing = db()->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
$existing->execute([
    'email' => $email,
    'id' => $userId,
]);

if ($existing->fetch()) {
    set_flash('error', 'Ya existe otra cuenta con ese correo.');
    redirect_to(app_url('/account/perfil.php'));
}

$statement = db()->prepare(
    'UPDATE users
     SET full_name = :full_name, email = :email, phone = :phone, updated_at = NOW()
     WHERE id = :id
     LIMIT 1'
);
$statement->execute([
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone !== '' ? $phone : null,
    'id' => $userId,
]);

$user = db()->prepare('SELECT id, full_name, email, phone, role FROM users WHERE id = :id LIMIT 1');
$user->execute(['id' => $userId]);
$_SESSION['user'] = $user->fetch();

set_flash('success', 'Perfil actualizado correctamente.');
redirect_to(app_url('/account/perfil.php'));
