<?php
require_once "app_helpers.php";
require_once "db.php";

$token = $_GET['token'] ?? '';
$row = $token ? app_consume_auth_token($conn, $token, 'email_verify') : null;

if ($row) {
    $stmt = $conn->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ? AND email_verified_at IS NULL");
    if ($stmt) {
        $stmt->bind_param('i', $row['user_id']);
        $stmt->execute();
        $stmt->close();
    }

    $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
    $userStmt->bind_param('i', $row['user_id']);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    $_SESSION['user_id'] = (int) $row['user_id'];
    $_SESSION['user'] = $user['username'] ?? 'user';
    app_add_notification($conn, (int) $row['user_id'], 'Email verified', 'Your account is now verified.', 'success', 'profile.php');
    app_flash('success', 'Email verified successfully.');
    header('Location: index.php');
    exit();
}

app_flash('warning', 'That verification link is invalid or expired.');
header('Location: resend_verification.php');
exit();
