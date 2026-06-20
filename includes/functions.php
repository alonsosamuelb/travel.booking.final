<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    static $basePath;

    if ($basePath === null) {
        $documentRoot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        $appRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

        if ($documentRoot !== '' && str_starts_with($appRoot, $documentRoot)) {
            $basePath = substr($appRoot, strlen($documentRoot));
        } else {
            $basePath = '/intermodulares/Travel_Booking_System_PHP_Simple';
        }

        $basePath = '/' . trim((string) $basePath, '/');
    }

    return $basePath . $path;
}

function app_origin(): string
{
    $https = (string) ($_SERVER['HTTPS'] ?? '');
    $forwardedProto = (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
    $scheme = ($https !== '' && strtolower($https) !== 'off') || strtolower($forwardedProto) === 'https'
        ? 'https'
        : 'http';

    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));

    if ($host === '') {
        $serverName = trim((string) ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        $serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
        $isDefaultPort = ($scheme === 'http' && $serverPort === '80') || ($scheme === 'https' && $serverPort === '443');
        $host = $serverName !== '' ? $serverName : 'localhost';

        if ($serverPort !== '' && !$isDefaultPort && !str_contains($host, ':')) {
            $host .= ':' . $serverPort;
        }
    }

    return $scheme . '://' . $host;
}

function app_absolute_url(string $path = ''): string
{
    return rtrim(app_origin(), '/') . app_url($path);
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function session_timeout_seconds(): int
{
    return 1800;
}

function current_session_fingerprint(): string
{
    $userAgent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
    return hash('sha256', $userAgent);
}

function refresh_authenticated_session(array $user): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = $user;
    $_SESSION['authenticated_at'] = time();
    $_SESSION['last_activity_at'] = time();
    $_SESSION['session_fingerprint'] = current_session_fingerprint();
}

function clear_authenticated_session(?string $flashType = null, ?string $flashMessage = null): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    unset(
        $_SESSION['user'],
        $_SESSION['authenticated_at'],
        $_SESSION['last_activity_at'],
        $_SESSION['session_fingerprint']
    );

    if ($flashType !== null && $flashMessage !== null) {
        $_SESSION['flash'] = [
            'type' => $flashType,
            'message' => $flashMessage,
        ];
    }

    session_regenerate_id(true);
}

function enforce_authenticated_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['user'])) {
        return;
    }

    $now = time();
    $lastActivity = (int) ($_SESSION['last_activity_at'] ?? 0);
    $fingerprint = (string) ($_SESSION['session_fingerprint'] ?? '');
    $expectedFingerprint = current_session_fingerprint();

    if ($fingerprint !== '' && !hash_equals($fingerprint, $expectedFingerprint)) {
        clear_authenticated_session('error', 'La sesion se ha cerrado por seguridad. Vuelve a iniciar sesion.');
        return;
    }

    if ($lastActivity > 0 && ($now - $lastActivity) > session_timeout_seconds()) {
        clear_authenticated_session('error', 'Tu sesion ha caducado por inactividad. Inicia sesion de nuevo.');
        return;
    }

    $_SESSION['last_activity_at'] = $now;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Necesitas iniciar sesion para continuar.');
        redirect_to(app_url('/index.php'));
    }
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        set_flash('error', 'No tienes permisos para acceder al panel de administracion.');
        redirect_to(app_url('/trips/viajes.php'));
    }
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return 'Sin fecha';
    }

    $date = new DateTimeImmutable($value);
    return $date->format('d/m/Y H:i');
}

function reservation_date_key(?string $value): string
{
    if (!$value) {
        return '';
    }

    return (new DateTimeImmutable($value))->format('Y-m-d');
}

function reservation_week_key(?string $value): string
{
    if (!$value) {
        return '';
    }

    return (new DateTimeImmutable($value))->format('o-\WW');
}

function parse_money_input(?string $value): float
{
    $normalized = str_replace([' ', ','], ['', '.'], trim((string) $value));

    if ($normalized === '' || !is_numeric($normalized)) {
        return 0.0;
    }

    return round((float) $normalized, 2);
}

function format_money(float|int|string|null $amount): string
{
    return number_format((float) $amount, 2, ',', '.') . ' €';
}

function platform_booking_fee(): float
{
    return 1.00;
}

function off_platform_payment_methods_label(): string
{
    return 'Efectivo, Bizum o transferencia';
}

function trip_price_label(float|int|string|null $amount): string
{
    return (float) $amount > 0 ? 'Conductor: ' . format_money($amount) . ' / plaza' : 'Precio pendiente';
}

function reservation_driver_estimate(float|int|string|null $pricePerSeat, int $seats = 1): float
{
    return round(max(0, (float) $pricePerSeat) * max(1, $seats), 2);
}

function reservation_driver_estimate_label(float|int|string|null $pricePerSeat, int $seats = 1): string
{
    $amount = reservation_driver_estimate($pricePerSeat, $seats);
    return $amount > 0 ? 'Al conductor: ' . format_money($amount) : 'Importe conductor pendiente';
}

function reservation_total_label(float|int|string|null $amount): string
{
    return (float) $amount > 0 ? 'Cuota app: ' . format_money($amount) : 'Cuota pendiente';
}

function trip_uploads_dir(): string
{
    return dirname(__DIR__) . '/assets/images/uploads/';
}

function ensure_trip_uploads_dir(): string
{
    $uploadsDir = trip_uploads_dir();

    if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0777, true) && !is_dir($uploadsDir)) {
        throw new RuntimeException('No se pudo preparar la carpeta de imagenes de viajes.');
    }

    if (!is_writable($uploadsDir)) {
        @chmod($uploadsDir, 0777);
    }

    if (!is_writable($uploadsDir)) {
        throw new RuntimeException('La carpeta de imagenes no tiene permisos de escritura para Apache.');
    }

    return $uploadsDir;
}

function store_trip_uploaded_image(array $uploadedImage, string $prefix = 'trip'): string
{
    $uploadError = (int) ($uploadedImage['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($uploadError) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'La imagen supera el tamano maximo permitido por PHP.',
            UPLOAD_ERR_PARTIAL => 'La imagen se subio de forma parcial. Intentalo de nuevo.',
            UPLOAD_ERR_NO_FILE => 'No se recibio ninguna imagen en la subida.',
            default => 'No se pudo subir la imagen del viaje.',
        });
    }

    $extension = strtolower((string) pathinfo((string) ($uploadedImage['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('La imagen debe ser JPG, JPEG, PNG o WEBP.');
    }

    $uploadsDir = ensure_trip_uploads_dir();
    $filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destinationFile = $uploadsDir . $filename;

    if (!move_uploaded_file((string) ($uploadedImage['tmp_name'] ?? ''), $destinationFile)) {
        throw new RuntimeException('No se pudo guardar la imagen subida. Revisa los permisos de assets/images/uploads.');
    }

    return app_url('/assets/images/uploads/' . $filename);
}

function trip_uploaded_image_prefix(): string
{
    return app_url('/assets/images/uploads/');
}

function is_local_trip_uploaded_image(?string $imagePath): bool
{
    $value = trim((string) $imagePath);
    if ($value === '') {
        return false;
    }

    return str_starts_with($value, trip_uploaded_image_prefix());
}

function delete_trip_uploaded_image(?string $imagePath): void
{
    if (!is_local_trip_uploaded_image($imagePath)) {
        return;
    }

    $filename = basename((string) $imagePath);
    if ($filename === '' || $filename === '.' || $filename === '..') {
        return;
    }

    $absolutePath = trip_uploads_dir() . $filename;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function destination_slug(?string $destination): string
{
    $value = trim((string) $destination);
    if ($value === '') {
        return 'default';
    }

    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'default';
}

function destination_image(?string $destination, ?string $fallback = null): string
{
    $fallbackValue = trim((string) $fallback);
    $localUploadsPrefix = app_url('/assets/images/uploads/');

    if ($fallbackValue !== '' && str_starts_with($fallbackValue, $localUploadsPrefix)) {
        return $fallbackValue;
    }

    $slug = destination_slug($destination);
    $baseDir = dirname(__DIR__) . '/assets/images/destinations/';
    $baseUrl = app_url('/assets/images/destinations/');

    foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
        $candidate = $slug . '.' . $extension;
        if (file_exists($baseDir . $candidate)) {
            return $baseUrl . $candidate;
        }
    }

    foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
        $candidate = 'default.' . $extension;
        if (file_exists($baseDir . $candidate)) {
            return $baseUrl . $candidate;
        }
    }
    return app_url('/assets/images/logo.png');
}

function vehicle_group_from_vehicle(?string $vehicle): string
{
    return stripos((string) $vehicle, 'van') !== false ? 'van' : 'car';
}

function local_destination_images(bool $includeDefault = false): array
{
    $baseDir = dirname(__DIR__) . '/assets/images/destinations/';
    $baseUrl = app_url('/assets/images/destinations/');

    if (!is_dir($baseDir)) {
        return [];
    }

    $files = glob($baseDir . '*.{jpg,jpeg,png,webp,avif}', GLOB_BRACE) ?: [];
    $images = [];

    foreach ($files as $file) {
        $filename = basename($file);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        if (!$includeDefault && $name === 'default') {
            continue;
        }

        $images[] = $baseUrl . $filename;
    }

    sort($images);
    return $images;
}

function random_project_hero_image(?string $fallback = null): string
{
    $images = local_destination_images();

    if ($images !== []) {
        return $images[random_int(0, count($images) - 1)];
    }

    return (string) ($fallback ?: app_url('/assets/images/logo.png'));
}

function sync_completed_reservations(): void
{
    $statement = db()->prepare(
        'UPDATE reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         SET r.status = "completed", r.updated_at = NOW()
         WHERE r.status = "active" AND t.departure_at < NOW()'
    );
    $statement->execute();
}

function remaining_seats(array $trip): int
{
    return max(0, (int) $trip['available_seats'] - (int) $trip['reserved_seats']);
}

function trip_status_label(string $status): string
{
    return match ($status) {
        'draft' => 'Borrador',
        'published' => 'Publicado',
        default => ucfirst($status),
    };
}

function reservation_status_label(string $status): string
{
    return match ($status) {
        'active' => 'Activa',
        'cancelled' => 'Cancelada',
        'completed' => 'Completada',
        default => ucfirst($status),
    };
}

function payment_status_label(string $status): string
{
    return match ($status) {
        'pending' => 'Cuota pendiente',
        'paid' => 'Cuota pagada',
        'refunded' => 'Cuota reembolsada',
        default => ucfirst($status),
    };
}

function fetch_trips(array $filters = [], bool $includeDrafts = false, ?int $ownerId = null): array
{
    sync_completed_reservations();

    $where = [$includeDrafts ? '1=1' : 't.status = "published"'];
    $params = [];

    if (!$includeDrafts && $ownerId === null) {
        $where[] = 't.departure_at >= NOW()';
    }

    if ($ownerId !== null) {
        $where[] = 't.creator_user_id = :owner_id';
        $params['owner_id'] = $ownerId;
    }

    if (!empty($filters['search'])) {
        $where[] = '(
            t.name LIKE :search_name
            OR t.description LIKE :search_description
            OR t.origin LIKE :search_origin
            OR t.destination LIKE :search_destination
        )';
        $likeSearch = '%' . trim((string) $filters['search']) . '%';
        $params['search_name'] = $likeSearch;
        $params['search_description'] = $likeSearch;
        $params['search_origin'] = $likeSearch;
        $params['search_destination'] = $likeSearch;
    }

    if (!empty($filters['origin'])) {
        $where[] = 't.origin LIKE :origin';
        $params['origin'] = '%' . trim((string) $filters['origin']) . '%';
    }

    if (!empty($filters['destination'])) {
        $where[] = 't.destination LIKE :destination';
        $params['destination'] = '%' . trim((string) $filters['destination']) . '%';
    }

    if (!empty($filters['departure_date'])) {
        $where[] = 'DATE(t.departure_at) = :departure_date';
        $params['departure_date'] = trim((string) $filters['departure_date']);
    }

    if (!empty($filters['vehicle_group'])) {
        if ($filters['vehicle_group'] === 'van') {
            $where[] = 'LOWER(t.vehicle) LIKE :vehicle_group';
            $params['vehicle_group'] = '%van%';
        }

        if ($filters['vehicle_group'] === 'car') {
            $where[] = 'LOWER(t.vehicle) NOT LIKE :vehicle_group';
            $params['vehicle_group'] = '%van%';
        }
    }

    $statement = db()->prepare(
        'SELECT
            t.*,
            u.full_name AS creator_name,
            COALESCE(SUM(CASE WHEN r.status = "active" THEN r.seats_reserved ELSE 0 END), 0) AS reserved_seats
         FROM trips t
         LEFT JOIN users u ON u.id = t.creator_user_id
         LEFT JOIN reservations r ON r.trip_id = t.id
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY t.id
         ORDER BY t.departure_at ASC'
    );

    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }

    $statement->execute();
    $trips = $statement->fetchAll();

    $minimumSeats = max(1, (int) ($filters['seats_min'] ?? 1));
    return array_values(array_filter($trips, static fn (array $trip): bool => remaining_seats($trip) >= $minimumSeats));
}

function find_trip(int $tripId): ?array
{
    $statement = db()->prepare(
        'SELECT
            t.*,
            u.full_name AS creator_name,
            u.email AS creator_email,
            u.phone AS creator_phone,
            u.role AS creator_role,
            COALESCE(SUM(CASE WHEN r.status = "active" THEN r.seats_reserved ELSE 0 END), 0) AS reserved_seats
         FROM trips t
         LEFT JOIN users u ON u.id = t.creator_user_id
         LEFT JOIN reservations r ON r.trip_id = t.id
         WHERE t.id = :id
         GROUP BY t.id
         LIMIT 1'
    );
    $statement->execute(['id' => $tripId]);
    $trip = $statement->fetch();

    return $trip ?: null;
}

function trip_driver_name(array $trip): string
{
    $name = trim((string) ($trip['creator_name'] ?? ''));
    return $name !== '' ? $name : 'Conductor no especificado';
}

function trip_detail_url(array $trip): string
{
    return app_url('/trips/viaje.php?id=' . (int) ($trip['id'] ?? 0));
}

function my_reservations(int $userId): array
{
    sync_completed_reservations();

    $statement = db()->prepare(
        'SELECT
            r.*,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at,
            t.vehicle,
            t.image_path,
            t.available_seats,
            t.creator_user_id,
            owner.full_name AS creator_name,
            owner.email AS creator_email
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         LEFT JOIN users owner ON owner.id = t.creator_user_id
         WHERE r.user_id = :user_id
         ORDER BY t.departure_at DESC'
    );
    $statement->execute(['user_id' => $userId]);
    return $statement->fetchAll();
}

function find_user_reservation(int $reservationId, int $userId): ?array
{
    sync_completed_reservations();

    $statement = db()->prepare(
        'SELECT
            r.*,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at,
            t.vehicle,
            t.image_path,
            t.creator_user_id,
            owner.full_name AS creator_name,
            owner.email AS creator_email
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         LEFT JOIN users owner ON owner.id = t.creator_user_id
         WHERE r.id = :reservation_id
           AND r.user_id = :user_id
         LIMIT 1'
    );
    $statement->execute([
        'reservation_id' => $reservationId,
        'user_id' => $userId,
    ]);
    $reservation = $statement->fetch();

    return $reservation ?: null;
}

function user_can_edit_reservation(array $reservation): bool
{
    if ((string) ($reservation['status'] ?? '') !== 'active') {
        return false;
    }

    $departureAt = strtotime((string) ($reservation['departure_at'] ?? ''));
    return $departureAt !== false && $departureAt >= time();
}

function user_active_reservation_rules(int $userId): array
{
    sync_completed_reservations();

    $statement = db()->prepare(
        'SELECT
            r.id,
            r.seats_reserved,
            t.departure_at,
            t.vehicle
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.user_id = :user_id
           AND r.status = "active"
           AND r.payment_status = "paid"
           AND t.departure_at >= NOW()
         ORDER BY t.departure_at ASC'
    );
    $statement->execute(['user_id' => $userId]);
    $rows = $statement->fetchAll();

    $sameDayCounts = [];
    $vehicleWeekCounts = [];
    $weeklySeats = [];

    foreach ($rows as $row) {
        $dateKey = reservation_date_key((string) $row['departure_at']);
        $weekKey = reservation_week_key((string) $row['departure_at']);
        $vehicleGroup = vehicle_group_from_vehicle((string) $row['vehicle']);

        $sameDayCounts[$dateKey] = (int) ($sameDayCounts[$dateKey] ?? 0) + 1;
        $vehicleWeekCounts[$vehicleGroup . '|' . $weekKey] = (int) ($vehicleWeekCounts[$vehicleGroup . '|' . $weekKey] ?? 0) + 1;
        $weeklySeats[$weekKey] = (int) ($weeklySeats[$weekKey] ?? 0) + (int) $row['seats_reserved'];
    }

    return [
        'same_day_counts' => $sameDayCounts,
        'vehicle_week_counts' => $vehicleWeekCounts,
        'weekly_seats' => $weeklySeats,
        'active_total' => count($rows),
    ];
}

function page_is(string $filename): bool
{
    return basename($_SERVER['PHP_SELF']) === $filename;
}

function vehicle_group_is(string $group): bool
{
    return (string) ($_GET['vehicle_group'] ?? '') === $group;
}

function admin_pct_change(float $current, float $previous): int
{
    if ($previous == 0.0) {
        return $current > 0 ? 100 : 0;
    }

    return (int) round((($current - $previous) / $previous) * 100);
}

function admin_sidebar_links(string $active): array
{
    $links = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => app_url('/admin/admin.php')],
        ['key' => 'users', 'label' => 'Usuarios', 'href' => app_url('/admin/usuarios.php')],
        ['key' => 'trips', 'label' => 'Viajes', 'href' => app_url('/admin/viajes.php')],
        ['key' => 'reservations', 'label' => 'Reservas', 'href' => app_url('/admin/reservas.php')],
        ['key' => 'commissions', 'label' => 'Comisiones', 'href' => app_url('/admin/comisiones.php')],
        ['key' => 'reports', 'label' => 'Informes', 'href' => app_url('/admin/informes.php')],
    ];

    foreach ($links as &$link) {
        $link['active'] = $link['key'] === $active;
    }
    unset($link);

    return $links;
}

function admin_dashboard_kpis(): array
{
    $currentUsers = (int) db()->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();
    $usersLast30 = (int) db()->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
    $usersPrev30 = (int) db()->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();

    $currentTrips = (int) db()->query('SELECT COUNT(*) FROM trips WHERE status = "published"')->fetchColumn();
    $tripsLast30 = (int) db()->query('SELECT COUNT(*) FROM trips WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
    $tripsPrev30 = (int) db()->query('SELECT COUNT(*) FROM trips WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();

    $currentReservations = (int) db()->query('SELECT COUNT(*) FROM reservations WHERE status = "active"')->fetchColumn();
    $reservationsLast30 = (int) db()->query('SELECT COUNT(*) FROM reservations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();
    $reservationsPrev30 = (int) db()->query('SELECT COUNT(*) FROM reservations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn();

    $successCurrent = db()->query(
        'SELECT
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) AS completed_total,
            COALESCE(SUM(CASE WHEN r.status = "cancelled" THEN 1 ELSE 0 END), 0) AS cancelled_total
         FROM reservations r
         WHERE r.updated_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)'
    )->fetch();
    $successPrevious = db()->query(
        'SELECT
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) AS completed_total,
            COALESCE(SUM(CASE WHEN r.status = "cancelled" THEN 1 ELSE 0 END), 0) AS cancelled_total
         FROM reservations r
         WHERE r.updated_at >= DATE_SUB(NOW(), INTERVAL 180 DAY)
           AND r.updated_at < DATE_SUB(NOW(), INTERVAL 90 DAY)'
    )->fetch();

    $currentSuccessBase = (int) $successCurrent['completed_total'] + (int) $successCurrent['cancelled_total'];
    $previousSuccessBase = (int) $successPrevious['completed_total'] + (int) $successPrevious['cancelled_total'];
    $currentSuccessRate = $currentSuccessBase > 0 ? (int) round(((int) $successCurrent['completed_total'] / $currentSuccessBase) * 100) : 0;
    $previousSuccessRate = $previousSuccessBase > 0 ? (int) round(((int) $successPrevious['completed_total'] / $previousSuccessBase) * 100) : 0;

    return [
        [
            'label' => 'Usuarios activos',
            'value' => $currentUsers,
            'trend' => admin_pct_change((float) $usersLast30, (float) $usersPrev30),
            'context' => $usersLast30 . ' altas en 30 dias',
            'icon' => 'users',
            'tone' => 'blue',
        ],
        [
            'label' => 'Viajes publicados',
            'value' => $currentTrips,
            'trend' => admin_pct_change((float) $tripsLast30, (float) $tripsPrev30),
            'context' => $tripsLast30 . ' viajes creados en 30 dias',
            'icon' => 'trips',
            'tone' => 'teal',
        ],
        [
            'label' => 'Reservas activas',
            'value' => $currentReservations,
            'trend' => admin_pct_change((float) $reservationsLast30, (float) $reservationsPrev30),
            'context' => $reservationsLast30 . ' reservas creadas en 30 dias',
            'icon' => 'bookings',
            'tone' => 'amber',
        ],
        [
            'label' => 'Tasa de exito',
            'value' => $currentSuccessRate . '%',
            'trend' => admin_pct_change((float) $currentSuccessRate, (float) $previousSuccessRate),
            'context' => 'Completadas frente a canceladas',
            'icon' => 'success',
            'tone' => 'green',
        ],
    ];
}

function admin_dashboard_monthly_reservations(int $months = 6): array
{
    $months = max(3, $months);
    $start = (new DateTimeImmutable('first day of this month'))->modify('-' . ($months - 1) . ' months');

    $statement = db()->prepare(
        'SELECT DATE_FORMAT(reservation_date, "%Y-%m-01") AS month_key, COUNT(*) AS total
         FROM reservations
         WHERE reservation_date >= :start_date
         GROUP BY month_key
         ORDER BY month_key ASC'
    );
    $statement->execute(['start_date' => $start->format('Y-m-d H:i:s')]);
    $rows = $statement->fetchAll();
    $indexed = [];

    foreach ($rows as $row) {
        $indexed[(string) $row['month_key']] = (int) $row['total'];
    }

    $series = [];

    for ($i = 0; $i < $months; $i++) {
        $month = $start->modify('+' . $i . ' months');
        $key = $month->format('Y-m-01');
        $series[] = [
            'label' => $month->format('M Y'),
            'value' => (int) ($indexed[$key] ?? 0),
        ];
    }

    return $series;
}

function admin_dashboard_status_breakdown(): array
{
    $statement = db()->query(
        'SELECT status, COUNT(*) AS total
         FROM reservations
         GROUP BY status'
    );

    $base = [
        'active' => 0,
        'completed' => 0,
        'cancelled' => 0,
    ];

    foreach ($statement->fetchAll() as $row) {
        $status = (string) $row['status'];
        if (array_key_exists($status, $base)) {
            $base[$status] = (int) $row['total'];
        }
    }

    return $base;
}

function admin_paginate(int $page, int $perPage): array
{
    $page = max(1, $page);
    $perPage = max(1, $perPage);

    return [
        'page' => $page,
        'per_page' => $perPage,
        'offset' => ($page - 1) * $perPage,
    ];
}

function admin_users(array $filters = []): array
{
    $search = trim((string) ($filters['search'] ?? ''));
    $status = (string) ($filters['status'] ?? '');
    $pager = admin_paginate((int) ($filters['page'] ?? 1), 8);

    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = '(full_name LIKE :search_full_name OR email LIKE :search_email)';
        $likeSearch = '%' . $search . '%';
        $params['search_full_name'] = $likeSearch;
        $params['search_email'] = $likeSearch;
    }

    if ($status === 'active') {
        $where[] = 'deleted_at IS NULL';
    } elseif ($status === 'inactive') {
        $where[] = 'deleted_at IS NOT NULL';
    }

    $count = db()->prepare('SELECT COUNT(*) FROM users WHERE ' . implode(' AND ', $where));
    foreach ($params as $key => $value) {
        $count->bindValue(':' . $key, $value);
    }
    $count->execute();
    $total = (int) $count->fetchColumn();

    $sql = 'SELECT id, full_name, email, phone, role, deleted_at, created_at
            FROM users
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset';
    $statement = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }
    $statement->bindValue(':limit', $pager['per_page'], PDO::PARAM_INT);
    $statement->bindValue(':offset', $pager['offset'], PDO::PARAM_INT);
    $statement->execute();

    return [
        'items' => $statement->fetchAll(),
        'total' => $total,
        'page' => $pager['page'],
        'per_page' => $pager['per_page'],
        'pages' => max(1, (int) ceil($total / $pager['per_page'])),
    ];
}

function admin_trips(array $filters = []): array
{
    $search = trim((string) ($filters['search'] ?? ''));
    $status = (string) ($filters['status'] ?? '');
    $pager = admin_paginate((int) ($filters['page'] ?? 1), 8);

    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = '(
            t.name LIKE :search_trip_name
            OR t.origin LIKE :search_trip_origin
            OR t.destination LIKE :search_trip_destination
            OR u.full_name LIKE :search_creator_name
        )';
        $likeSearch = '%' . $search . '%';
        $params['search_trip_name'] = $likeSearch;
        $params['search_trip_origin'] = $likeSearch;
        $params['search_trip_destination'] = $likeSearch;
        $params['search_creator_name'] = $likeSearch;
    }

    if ($status !== '') {
        $where[] = 't.status = :status';
        $params['status'] = $status;
    }

    $count = db()->prepare('SELECT COUNT(*) FROM trips t LEFT JOIN users u ON u.id = t.creator_user_id WHERE ' . implode(' AND ', $where));
    foreach ($params as $key => $value) {
        $count->bindValue(':' . $key, $value);
    }
    $count->execute();
    $total = (int) $count->fetchColumn();

    $sql = 'SELECT
                t.*,
                u.full_name AS creator_name,
                COALESCE(SUM(CASE WHEN r.status = "active" THEN r.seats_reserved ELSE 0 END), 0) AS reserved_seats
            FROM trips t
            LEFT JOIN users u ON u.id = t.creator_user_id
            LEFT JOIN reservations r ON r.trip_id = t.id
            WHERE ' . implode(' AND ', $where) . '
            GROUP BY t.id
            ORDER BY t.departure_at DESC
            LIMIT :limit OFFSET :offset';
    $statement = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }
    $statement->bindValue(':limit', $pager['per_page'], PDO::PARAM_INT);
    $statement->bindValue(':offset', $pager['offset'], PDO::PARAM_INT);
    $statement->execute();

    return [
        'items' => $statement->fetchAll(),
        'total' => $total,
        'page' => $pager['page'],
        'per_page' => $pager['per_page'],
        'pages' => max(1, (int) ceil($total / $pager['per_page'])),
    ];
}

function admin_reservations(array $filters = []): array
{
    sync_completed_reservations();

    $search = trim((string) ($filters['search'] ?? ''));
    $status = (string) ($filters['status'] ?? '');
    $pager = admin_paginate((int) ($filters['page'] ?? 1), 8);

    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = '(
            u.full_name LIKE :search_user_name
            OR u.email LIKE :search_user_email
            OR t.name LIKE :search_trip_name
            OR t.origin LIKE :search_trip_origin
            OR t.destination LIKE :search_trip_destination
        )';
        $likeSearch = '%' . $search . '%';
        $params['search_user_name'] = $likeSearch;
        $params['search_user_email'] = $likeSearch;
        $params['search_trip_name'] = $likeSearch;
        $params['search_trip_origin'] = $likeSearch;
        $params['search_trip_destination'] = $likeSearch;
    }

    if ($status !== '') {
        $where[] = 'r.status = :status';
        $params['status'] = $status;
    }

    $count = db()->prepare(
        'SELECT COUNT(*)
         FROM reservations r
         INNER JOIN users u ON u.id = r.user_id
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE ' . implode(' AND ', $where)
    );
    foreach ($params as $key => $value) {
        $count->bindValue(':' . $key, $value);
    }
    $count->execute();
    $total = (int) $count->fetchColumn();

    $sql = 'SELECT
                r.*,
                u.full_name,
                u.email,
                t.name AS trip_name,
                t.origin,
                t.destination,
                t.departure_at,
                t.vehicle
            FROM reservations r
            INNER JOIN users u ON u.id = r.user_id
            INNER JOIN trips t ON t.id = r.trip_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY t.departure_at DESC
            LIMIT :limit OFFSET :offset';
    $statement = db()->prepare($sql);
    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }
    $statement->bindValue(':limit', $pager['per_page'], PDO::PARAM_INT);
    $statement->bindValue(':offset', $pager['offset'], PDO::PARAM_INT);
    $statement->execute();

    return [
        'items' => $statement->fetchAll(),
        'total' => $total,
        'page' => $pager['page'],
        'per_page' => $pager['per_page'],
        'pages' => max(1, (int) ceil($total / $pager['per_page'])),
    ];
}

function admin_commission_filters(array $filters = []): array
{
    $search = trim((string) ($filters['search'] ?? ''));
    $paymentStatus = (string) ($filters['payment_status'] ?? '');

    $where = ['r.total_price > 0'];
    $params = [];

    if ($search !== '') {
        $where[] = '(
            passenger.full_name LIKE :search_passenger_name
            OR passenger.email LIKE :search_passenger_email
            OR driver.full_name LIKE :search_driver_name
            OR driver.email LIKE :search_driver_email
            OR t.name LIKE :search_trip_name
            OR t.origin LIKE :search_trip_origin
            OR t.destination LIKE :search_trip_destination
        )';
        $likeSearch = '%' . $search . '%';
        $params['search_passenger_name'] = $likeSearch;
        $params['search_passenger_email'] = $likeSearch;
        $params['search_driver_name'] = $likeSearch;
        $params['search_driver_email'] = $likeSearch;
        $params['search_trip_name'] = $likeSearch;
        $params['search_trip_origin'] = $likeSearch;
        $params['search_trip_destination'] = $likeSearch;
    }

    if ($paymentStatus !== '') {
        $where[] = 'r.payment_status = :payment_status';
        $params['payment_status'] = $paymentStatus;
    }

    return [
        'where' => $where,
        'params' => $params,
    ];
}

function admin_commission_summary(array $filters = []): array
{
    $prepared = admin_commission_filters($filters);
    $statement = db()->prepare(
        'SELECT
            COUNT(*) AS commissions_total,
            COALESCE(SUM(CASE WHEN r.payment_status = "paid" THEN r.total_price ELSE 0 END), 0) AS paid_amount_total,
            COALESCE(SUM(CASE WHEN r.payment_status = "pending" THEN r.total_price ELSE 0 END), 0) AS pending_amount_total,
            COALESCE(SUM(CASE WHEN r.payment_status = "refunded" THEN r.total_price ELSE 0 END), 0) AS refunded_amount_total,
            COALESCE(SUM(CASE WHEN r.payment_status = "paid" THEN 1 ELSE 0 END), 0) AS paid_count,
            COALESCE(SUM(CASE WHEN r.payment_status = "pending" THEN 1 ELSE 0 END), 0) AS pending_count,
            COALESCE(SUM(CASE WHEN r.payment_status = "refunded" THEN 1 ELSE 0 END), 0) AS refunded_count,
            MAX(r.paid_at) AS last_paid_at
         FROM reservations r
         INNER JOIN users passenger ON passenger.id = r.user_id
         INNER JOIN trips t ON t.id = r.trip_id
         LEFT JOIN users driver ON driver.id = t.creator_user_id
         WHERE ' . implode(' AND ', $prepared['where'])
    );

    foreach ($prepared['params'] as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }
    $statement->execute();

    return $statement->fetch() ?: [];
}

function admin_commissions(array $filters = []): array
{
    $prepared = admin_commission_filters($filters);
    $pager = admin_paginate((int) ($filters['page'] ?? 1), 8);

    $count = db()->prepare(
        'SELECT COUNT(*)
         FROM reservations r
         INNER JOIN users passenger ON passenger.id = r.user_id
         INNER JOIN trips t ON t.id = r.trip_id
         LEFT JOIN users driver ON driver.id = t.creator_user_id
         WHERE ' . implode(' AND ', $prepared['where'])
    );
    foreach ($prepared['params'] as $key => $value) {
        $count->bindValue(':' . $key, $value);
    }
    $count->execute();
    $total = (int) $count->fetchColumn();

    $statement = db()->prepare(
        'SELECT
            r.id,
            r.trip_id,
            r.user_id,
            r.total_price,
            r.payment_status,
            r.paid_at,
            r.created_at,
            r.updated_at,
            r.stripe_payment_intent_id,
            r.stripe_checkout_session_id,
            passenger.full_name AS passenger_name,
            passenger.email AS passenger_email,
            driver.id AS driver_id,
            driver.full_name AS driver_name,
            driver.email AS driver_email,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at
         FROM reservations r
         INNER JOIN users passenger ON passenger.id = r.user_id
         INNER JOIN trips t ON t.id = r.trip_id
         LEFT JOIN users driver ON driver.id = t.creator_user_id
         WHERE ' . implode(' AND ', $prepared['where']) . '
         ORDER BY COALESCE(r.paid_at, r.created_at) DESC, r.id DESC
         LIMIT :limit OFFSET :offset'
    );

    foreach ($prepared['params'] as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }
    $statement->bindValue(':limit', $pager['per_page'], PDO::PARAM_INT);
    $statement->bindValue(':offset', $pager['offset'], PDO::PARAM_INT);
    $statement->execute();

    return [
        'items' => $statement->fetchAll(),
        'total' => $total,
        'page' => $pager['page'],
        'per_page' => $pager['per_page'],
        'pages' => max(1, (int) ceil($total / $pager['per_page'])),
    ];
}

function admin_find_user(int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT id, full_name, email, phone, role, deleted_at
         FROM users
         WHERE id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    return $user ?: null;
}

function admin_user_options(): array
{
    $statement = db()->query(
        'SELECT id, full_name, email, deleted_at
         FROM users
         ORDER BY deleted_at IS NOT NULL, full_name ASC'
    );

    return $statement->fetchAll();
}

function admin_trip_options(): array
{
    $statement = db()->query(
        'SELECT id, name, origin, destination, departure_at, status, price_per_seat
         FROM trips
         ORDER BY departure_at DESC'
    );

    return $statement->fetchAll();
}

function admin_find_reservation(int $reservationId): ?array
{
    $statement = db()->prepare(
        'SELECT
            r.*,
            u.full_name,
            u.email,
            t.name AS trip_name,
            t.origin,
            t.destination,
            t.departure_at,
            t.available_seats,
            t.vehicle
         FROM reservations r
         INNER JOIN users u ON u.id = r.user_id
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE r.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $reservationId]);
    $reservation = $statement->fetch();

    return $reservation ?: null;
}

function admin_reservation_report_summary(): array
{
    sync_completed_reservations();

    $summary = db()->query(
        'SELECT
            COUNT(*) AS past_reservations_total,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) AS completed_reservations_total,
            COALESCE(SUM(CASE WHEN r.status = "cancelled" THEN 1 ELSE 0 END), 0) AS cancelled_reservations_total,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN r.seats_reserved ELSE 0 END), 0) AS travelled_passengers_total,
            COUNT(DISTINCT CASE WHEN r.status = "completed" THEN r.trip_id END) AS successful_trips_total,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN r.total_price ELSE 0 END), 0) AS collected_revenue_total
         FROM reservations r
         INNER JOIN trips t ON t.id = r.trip_id
         WHERE t.departure_at < NOW()'
    )->fetch();

    $pastTotal = (int) ($summary['past_reservations_total'] ?? 0);
    $completedTotal = (int) ($summary['completed_reservations_total'] ?? 0);

    return [
        'past_reservations_total' => $pastTotal,
        'completed_reservations_total' => $completedTotal,
        'cancelled_reservations_total' => (int) ($summary['cancelled_reservations_total'] ?? 0),
        'travelled_passengers_total' => (int) ($summary['travelled_passengers_total'] ?? 0),
        'successful_trips_total' => (int) ($summary['successful_trips_total'] ?? 0),
        'collected_revenue_total' => (float) ($summary['collected_revenue_total'] ?? 0),
        'success_rate' => $pastTotal > 0 ? (int) round(($completedTotal / $pastTotal) * 100) : 0,
    ];
}

function admin_past_trip_reports(array $filters = []): array
{
    sync_completed_reservations();

    $search = trim((string) ($filters['search'] ?? ''));
    $status = trim((string) ($filters['status'] ?? ''));
    $pager = admin_paginate((int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 8));

    $where = ['t.departure_at < NOW()'];
    $params = [];
    $having = [];

    if ($search !== '') {
        $where[] = '(
            t.name LIKE :search_name
            OR t.origin LIKE :search_origin
            OR t.destination LIKE :search_destination
            OR t.vehicle LIKE :search_vehicle
            OR u.full_name LIKE :search_driver
            OR EXISTS (
                SELECT 1
                FROM reservations r2
                INNER JOIN users u2 ON u2.id = r2.user_id
                WHERE r2.trip_id = t.id
                  AND (u2.full_name LIKE :search_passenger_name OR u2.email LIKE :search_passenger_email)
            )
        )';
        $likeSearch = '%' . $search . '%';
        $params['search_name'] = $likeSearch;
        $params['search_origin'] = $likeSearch;
        $params['search_destination'] = $likeSearch;
        $params['search_vehicle'] = $likeSearch;
        $params['search_driver'] = $likeSearch;
        $params['search_passenger_name'] = $likeSearch;
        $params['search_passenger_email'] = $likeSearch;
    }

    if ($status === 'completed') {
        $having[] = 'COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) > 0';
    } elseif ($status === 'not_completed') {
        $having[] = 'COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) = 0';
    }

    $count = db()->prepare(
        'SELECT COUNT(*)
         FROM (
            SELECT t.id
         FROM trips t
         LEFT JOIN users u ON u.id = t.creator_user_id
         LEFT JOIN reservations r ON r.trip_id = t.id
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY t.id' . ($having ? ' HAVING ' . implode(' AND ', $having) : '') . '
         ) report_rows'
    );
    foreach ($params as $key => $value) {
        $count->bindValue(':' . $key, $value);
    }
    $count->execute();
    $total = (int) $count->fetchColumn();

    $statement = db()->prepare(
        'SELECT
            t.id,
            t.name,
            t.origin,
            t.destination,
            t.departure_at,
            t.vehicle,
            t.price_per_seat,
            u.full_name AS creator_name,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN r.seats_reserved ELSE 0 END), 0) AS travelled_passengers,
            COALESCE(SUM(CASE WHEN r.status = "cancelled" THEN 1 ELSE 0 END), 0) AS cancelled_reservations,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) AS completed_reservations,
            COUNT(r.id) AS total_reservations,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN r.total_price ELSE 0 END), 0) AS collected_amount
         FROM trips t
         LEFT JOIN users u ON u.id = t.creator_user_id
         LEFT JOIN reservations r ON r.trip_id = t.id
         WHERE ' . implode(' AND ', $where) . '
         GROUP BY t.id
         ' . ($having ? 'HAVING ' . implode(' AND ', $having) : '') . '
         ORDER BY t.departure_at DESC
         LIMIT :limit OFFSET :offset'
    );
    foreach ($params as $key => $value) {
        $statement->bindValue(':' . $key, $value);
    }
    $statement->bindValue(':limit', $pager['per_page'], PDO::PARAM_INT);
    $statement->bindValue(':offset', $pager['offset'], PDO::PARAM_INT);
    $statement->execute();

    return [
        'items' => $statement->fetchAll(),
        'total' => $total,
        'status' => $status,
        'page' => $pager['page'],
        'per_page' => $pager['per_page'],
        'pages' => max(1, (int) ceil($total / $pager['per_page'])),
    ];
}

function admin_past_trip_report_detail(int $tripId): ?array
{
    sync_completed_reservations();

    $statement = db()->prepare(
        'SELECT
            t.*,
            u.full_name AS creator_name,
            u.email AS creator_email,
            u.phone AS creator_phone,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN r.seats_reserved ELSE 0 END), 0) AS travelled_passengers,
            COALESCE(SUM(CASE WHEN r.status = "cancelled" THEN 1 ELSE 0 END), 0) AS cancelled_reservations,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN 1 ELSE 0 END), 0) AS completed_reservations,
            COUNT(r.id) AS total_reservations,
            COALESCE(SUM(CASE WHEN r.status = "completed" THEN r.total_price ELSE 0 END), 0) AS collected_amount
         FROM trips t
         LEFT JOIN users u ON u.id = t.creator_user_id
         LEFT JOIN reservations r ON r.trip_id = t.id
         WHERE t.id = :id
           AND t.departure_at < NOW()
         GROUP BY t.id
         LIMIT 1'
    );
    $statement->execute(['id' => $tripId]);
    $trip = $statement->fetch();

    if (!$trip) {
        return null;
    }

    $reservationsStatement = db()->prepare(
        'SELECT
            r.id,
            r.seats_reserved,
            r.price_per_seat,
            r.total_price,
            r.payment_status,
            r.status,
            r.notes,
            r.travel_role,
            r.created_at,
            u.full_name,
            u.email
         FROM reservations r
         INNER JOIN users u ON u.id = r.user_id
         WHERE r.trip_id = :trip_id
         ORDER BY r.created_at ASC'
    );
    $reservationsStatement->execute(['trip_id' => $tripId]);

    $trip['reservations'] = $reservationsStatement->fetchAll();

    return $trip;
}
