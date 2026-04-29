<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function app_current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function app_current_user_name(): ?string
{
    return isset($_SESSION['user']) ? (string) $_SESSION['user'] : null;
}

function app_is_logged_in(): bool
{
    return app_current_user_id() !== null;
}

function app_require_login(string $redirect = 'login.php'): void
{
    if (!app_is_logged_in()) {
        app_flash('warning', 'Please log in to use that feature.');
        header('Location: ' . $redirect);
        exit();
    }
}

function app_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function app_take_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function app_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_track_recent_view(mysqli $conn, int $userId, int $gameId): void
{
    $stmt = $conn->prepare(
        "INSERT INTO recently_viewed (user_id, game_id, viewed_at)
         VALUES (?, ?, NOW())
         ON DUPLICATE KEY UPDATE viewed_at = VALUES(viewed_at)"
    );

    if ($stmt) {
        $stmt->bind_param('ii', $userId, $gameId);
        $stmt->execute();
        $stmt->close();
    }
}

function app_user_pref_theme(mysqli $conn, int $userId): string
{
    $stmt = $conn->prepare("SELECT theme_preference FROM users WHERE id = ? LIMIT 1");
    if (!$stmt) {
        return 'dark';
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    $theme = $row['theme_preference'] ?? 'dark';
    return in_array($theme, ['dark', 'light'], true) ? $theme : 'dark';
}

function app_set_user_pref_theme(mysqli $conn, int $userId, string $theme): void
{
    if (!in_array($theme, ['dark', 'light'], true)) {
        return;
    }

    $stmt = $conn->prepare("UPDATE users SET theme_preference = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('si', $theme, $userId);
        $stmt->execute();
        $stmt->close();
    }
}

function app_add_notification(mysqli $conn, int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
{
    $stmt = $conn->prepare(
        "INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param('issss', $userId, $title, $message, $type, $link);
        $stmt->execute();
        $stmt->close();
    }
}

function app_unread_notification_count(mysqli $conn, int $userId): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return isset($row['total']) ? (int) $row['total'] : 0;
}

function app_create_auth_token(mysqli $conn, int $userId, string $purpose, int $minutes = 60): string
{
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + ($minutes * 60));

    $stmt = $conn->prepare(
        "INSERT INTO auth_tokens (user_id, purpose, token_hash, expires_at) VALUES (?, ?, ?, ?)"
    );
    if ($stmt) {
        $stmt->bind_param('isss', $userId, $purpose, $hash, $expiresAt);
        $stmt->execute();
        $stmt->close();
    }

    return $token;
}

function app_consume_auth_token(mysqli $conn, string $token, string $purpose): ?array
{
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare(
        "SELECT id, user_id, purpose, expires_at, used_at
         FROM auth_tokens
         WHERE token_hash = ? AND purpose = ? AND used_at IS NULL AND expires_at > NOW()
         LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('ss', $hash, $purpose);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }

    $update = $conn->prepare("UPDATE auth_tokens SET used_at = NOW() WHERE id = ?");
    if ($update) {
        $update->bind_param('i', $row['id']);
        $update->execute();
        $update->close();
    }

    return $row;
}

function app_fetch_auth_token(mysqli $conn, string $token, string $purpose): ?array
{
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare(
        "SELECT id, user_id, purpose, expires_at, used_at
         FROM auth_tokens
         WHERE token_hash = ? AND purpose = ? AND used_at IS NULL AND expires_at > NOW()
         LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('ss', $hash, $purpose);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}
