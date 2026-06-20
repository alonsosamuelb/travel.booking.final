<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

clear_authenticated_session('success', 'Sesion cerrada.');
redirect_to(app_url('/index.php'));
