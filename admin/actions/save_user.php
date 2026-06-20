<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(app_url('/admin/usuarios.php'));
}

$userId = (int) ($_POST['user_id'] ?? 0);
$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$role = (string) ($_POST['role'] ?? 'user');
$accountStatus = (string) ($_POST['account_status'] ?? 'active');
$currentUserId = (int) current_user()['id'];

if ($fullName === '' || $email === '') {
    set_flash('error', 'Nombre y correo son obligatorios.');
    redirect_to(app_url($userId > 0 ? '/admin/usuario-form.php?id=' . $userId : '/admin/usuario-form.php'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Introduce un correo valido.');
    redirect_to(app_url($userId > 0 ? '/admin/usuario-form.php?id=' . $userId : '/admin/usuario-form.php'));
}

if ($role !== 'admin') {
    $role = 'user';
}

if ($userId === 0 && strlen($password) < 6) {
    set_flash('error', 'La contrasena inicial debe tener al menos 6 caracteres.');
    redirect_to(app_url('/admin/usuario-form.php'));
}

$existing = db()->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
$existing->execute([
    'email' => $email,
    'id' => $userId,
]);

if ($existing->fetch()) {
    set_flash('error', 'Ya existe otro usuario con ese correo.');
    redirect_to(app_url($userId > 0 ? '/admin/usuario-form.php?id=' . $userId : '/admin/usuario-form.php'));
}

if ($userId > 0) {
    $user = admin_find_user($userId);

    if (!$user) {
        set_flash('error', 'No se encontro el usuario.');
        redirect_to(app_url('/admin/usuarios.php'));
    }

    if ($userId === $currentUserId && $role !== 'admin') {
        set_flash('error', 'No puedes quitarte el rol de administrador a ti mismo.');
        redirect_to(app_url('/admin/usuario-form.php?id=' . $userId));
    }

    if ($userId === $currentUserId && $accountStatus !== 'active') {
        set_flash('error', 'No puedes desactivarte a ti mismo desde esta edicion.');
        redirect_to(app_url('/admin/usuario-form.php?id=' . $userId));
    }

    $sql = 'UPDATE users
            SET full_name = :full_name,
                email = :email,
                phone = :phone,
                role = :role,
                deleted_at = :deleted_at,
                updated_at = NOW()';
    $params = [
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone !== '' ? $phone : null,
        'role' => $role,
        'deleted_at' => $accountStatus === 'inactive' ? date('Y-m-d H:i:s') : null,
        'id' => $userId,
    ];

    if ($password !== '') {
        if (strlen($password) < 6) {
            set_flash('error', 'La nueva contrasena debe tener al menos 6 caracteres.');
            redirect_to(app_url('/admin/usuario-form.php?id=' . $userId));
        }

        $sql .= ', password = :password';
        $params['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    $sql .= ' WHERE id = :id LIMIT 1';
    $statement = db()->prepare($sql);
    $statement->execute($params);

    if ($userId === $currentUserId) {
        $refresh = db()->prepare('SELECT id, full_name, email, phone, role FROM users WHERE id = :id LIMIT 1');
        $refresh->execute(['id' => $userId]);
        $_SESSION['user'] = $refresh->fetch();
    }

    set_flash('success', 'Usuario actualizado correctamente.');
    redirect_to(app_url('/admin/usuarios.php'));
}

$statement = db()->prepare(
    'INSERT INTO users (full_name, email, password, phone, role, deleted_at, created_at, updated_at)
     VALUES (:full_name, :email, :password, :phone, :role, :deleted_at, NOW(), NOW())'
);
$statement->execute([
    'full_name' => $fullName,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'phone' => $phone !== '' ? $phone : null,
    'role' => $role,
    'deleted_at' => $accountStatus === 'inactive' ? date('Y-m-d H:i:s') : null,
]);

set_flash('success', 'Usuario creado correctamente.');
redirect_to(app_url('/admin/usuarios.php'));
