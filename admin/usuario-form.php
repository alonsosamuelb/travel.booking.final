<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$userId = max(0, (int) ($_GET['id'] ?? 0));
$editingUser = $userId > 0 ? admin_find_user($userId) : null;
$sidebarLinks = admin_sidebar_links('users');

if ($userId > 0 && !$editingUser) {
    set_flash('error', 'No se encontro el usuario a editar.');
    redirect_to(app_url('/admin/usuarios.php'));
}

$pageTitle = $editingUser ? 'Editar usuario' : 'Nuevo usuario';

include dirname(__DIR__) . '/includes/header.php';
?>

<section class="admin-executive-shell">
    <aside class="admin-sidebar card">
        <div class="admin-sidebar-top">
            <span class="admin-sidebar-eyebrow">Control center</span>
            <h2>Travel Admin</h2>
            <p class="muted">Gestion ejecutiva de usuarios, viajes, reservas e informes.</p>
        </div>
        <nav class="admin-sidebar-nav" aria-label="Navegacion del panel">
            <?php foreach ($sidebarLinks as $sidebarLink): ?>
                <a class="admin-sidebar-link<?= $sidebarLink['active'] ? ' is-active' : '' ?>" href="<?= e($sidebarLink['href']) ?>">
                    <span class="admin-sidebar-link-dot"></span>
                    <span><?= e($sidebarLink['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-foot">
            <div class="admin-sidebar-profile">
                <strong><?= e((string) current_user()['full_name']) ?></strong>
                <span>Administrador</span>
            </div>
            <a class="button button-soft-dark admin-sidebar-button" href="<?= app_url('/admin/usuarios.php') ?>">Volver a usuarios</a>
        </div>
    </aside>

    <div class="admin-dashboard">
    <section class="admin-hero-card card">
        <div class="card-body admin-hero-card-body">
            <div>
                <span class="admin-hero-badge">User editor</span>
                <h1><?= $editingUser ? 'Editar usuario' : 'Crear usuario' ?></h1>
                <p class="muted">Gestiona nombre, correo, telefono, rol y estado de la cuenta.</p>
            </div>
            <div class="admin-hero-meta">
                <div class="admin-hero-meta-card">
                    <span>Modulo</span>
                    <strong>Usuarios</strong>
                </div>
                <div class="admin-hero-meta-card">
                    <span>Modo</span>
                    <strong><?= $editingUser ? 'Edicion' : 'Alta' ?></strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card admin-panel-card">
        <div class="card-body">
            <form class="form-grid" method="post" action="<?= app_url('/admin/actions/save_user.php') ?>">
                <?php if ($editingUser): ?>
                    <input type="hidden" name="user_id" value="<?= (int) $editingUser['id'] ?>">
                <?php endif; ?>

                <div class="field-row">
                    <label>
                        Nombre completo
                        <input type="text" name="full_name" value="<?= e((string) ($editingUser['full_name'] ?? '')) ?>" required>
                    </label>
                    <label>
                        Correo electronico
                        <input type="email" name="email" value="<?= e((string) ($editingUser['email'] ?? '')) ?>" required>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        Telefono
                        <input type="text" name="phone" value="<?= e((string) ($editingUser['phone'] ?? '')) ?>" placeholder="Opcional">
                    </label>
                    <label>
                        Rol
                        <select name="role" required>
                            <option value="user"<?= (($editingUser['role'] ?? 'user') === 'user') ? ' selected' : '' ?>>Usuario</option>
                            <option value="admin"<?= (($editingUser['role'] ?? '') === 'admin') ? ' selected' : '' ?>>Administrador</option>
                        </select>
                    </label>
                </div>

                <div class="field-row">
                    <label>
                        <?= $editingUser ? 'Nueva contrasena' : 'Contrasena' ?>
                        <input type="password" name="password"<?= $editingUser ? '' : ' required' ?>>
                    </label>
                    <label>
                        Estado
                        <select name="account_status" required>
                            <option value="active"<?= empty($editingUser['deleted_at']) ? ' selected' : '' ?>>Activo</option>
                            <option value="inactive"<?= !empty($editingUser['deleted_at']) ? ' selected' : '' ?>>Inactivo</option>
                        </select>
                    </label>
                </div>

                <?php if ($editingUser): ?>
                    <p class="muted">Deja la contrasena vacia si no quieres cambiarla.</p>
                <?php endif; ?>

                <div class="toolbar">
                    <button class="button button-primary" type="submit"><?= $editingUser ? 'Guardar cambios' : 'Crear usuario' ?></button>
                    <a class="button button-ghost" href="<?= app_url('/admin/admin.php') ?>">Volver al panel</a>
                </div>
            </form>
        </div>
    </section>
    </div>
</section>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
