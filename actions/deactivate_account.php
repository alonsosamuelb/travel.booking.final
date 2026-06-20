<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/account/perfil.php'));
}

$userId = (int) current_user()['id'];

$statement = db()->prepare('UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id LIMIT 1');
$statement->execute(['id' => $userId]);

$_SESSION = [];
session_regenerate_id(true);

set_flash('success', 'Tu cuenta ha quedado inactiva. Puedes reactivarla cuando quieras.');
redirect_to(app_url('/auth/reactivar-cuenta.php'));
