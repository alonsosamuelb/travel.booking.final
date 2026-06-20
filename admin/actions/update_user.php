<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/admin/usuarios.php'));
}

$userId = (int) ($_POST['user_id'] ?? 0);
$action = (string) ($_POST['action_name'] ?? '');
$currentUserId = (int) current_user()['id'];

$statement = db()->prepare('SELECT id, role, deleted_at FROM users WHERE id = :id LIMIT 1');
$statement->execute(['id' => $userId]);
$user = $statement->fetch();

if (!$user) {
    set_flash('error', 'No se encontro el usuario.');
    redirect_to(app_url('/admin/usuarios.php'));
}

if ($action === 'set_role') {
    if ($userId === $currentUserId) {
        set_flash('error', 'No puedes cambiar tu propio rol mientras estas en sesion.');
        redirect_to(app_url('/admin/usuarios.php'));
    }

    $newRole = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
    $update = db()->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id LIMIT 1');
    $update->execute(['role' => $newRole, 'id' => $userId]);
    set_flash('success', 'Rol del usuario actualizado.');
    redirect_to(app_url('/admin/usuarios.php'));
}

if ($action === 'deactivate') {
    if ($userId === $currentUserId) {
        set_flash('error', 'No puedes darte de baja a ti mismo desde el panel.');
        redirect_to(app_url('/admin/usuarios.php'));
    }

    $update = db()->prepare('UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id LIMIT 1');
    $update->execute(['id' => $userId]);
    set_flash('success', 'Usuario dado de baja logicamente.');
    redirect_to(app_url('/admin/usuarios.php'));
}

if ($action === 'reactivate') {
    $update = db()->prepare('UPDATE users SET deleted_at = NULL, updated_at = NOW() WHERE id = :id LIMIT 1');
    $update->execute(['id' => $userId]);
    set_flash('success', 'Usuario reactivado.');
    redirect_to(app_url('/admin/usuarios.php'));
}

redirect_to(app_url('/admin/usuarios.php'));
