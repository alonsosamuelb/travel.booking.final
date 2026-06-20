<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/auth/olvide-contrasena.php'));
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

if ($email === '' || $password === '' || $passwordConfirmation === '') {
    set_flash('error', 'Completa correo y las dos contrasenas.');
    redirect_to(app_url('/auth/olvide-contrasena.php?email=' . urlencode($email)));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo electronico valido.');
    redirect_to(app_url('/auth/olvide-contrasena.php?email=' . urlencode($email)));
}

if (strlen($password) < 6) {
    set_flash('error', 'La contrasena debe tener al menos 6 caracteres.');
    redirect_to(app_url('/auth/olvide-contrasena.php?email=' . urlencode($email)));
}

if ($password !== $passwordConfirmation) {
    set_flash('error', 'Las contrasenas no coinciden.');
    redirect_to(app_url('/auth/olvide-contrasena.php?email=' . urlencode($email)));
}

$statement = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$statement->execute(['email' => $email]);
$user = $statement->fetch();

if (!$user) {
    set_flash('error', 'No existe una cuenta con ese correo. Puedes registrarte directamente.');
    redirect_to(app_url('/auth/registro.php?email=' . urlencode($email)));
}

$update = db()->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id LIMIT 1');
$update->execute([
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'id' => $user['id'],
]);

set_flash('success', 'Contrasena actualizada. Ya puedes iniciar sesion.');
redirect_to(app_url('/auth/login.php?email=' . urlencode($email)));
