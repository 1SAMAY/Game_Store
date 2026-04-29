<?php
require_once "app_helpers.php";
require_once "db.php";

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$message = '';
$validToken = $token ? app_fetch_auth_token($conn, $token, 'password_reset') : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$validToken) {
        $message = 'This reset link is invalid or expired.';
    } elseif ($password === '' || strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $hash, $validToken['user_id']);
            $stmt->execute();
            $stmt->close();
        }

        $consume = $conn->prepare("UPDATE auth_tokens SET used_at = NOW() WHERE id = ?");
        if ($consume) {
            $consume->bind_param('i', $validToken['id']);
            $consume->execute();
            $consume->close();
        }

        app_add_notification($conn, (int) $validToken['user_id'], 'Password updated', 'Your password was changed successfully.', 'success', 'login.php');
        app_flash('success', 'Password updated. Please log in again.');
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container py-5" style="max-width:720px;">
    <div class="req-card">
      <h1 class="h3 mb-3">Set a New Password</h1>
      <?php if ($message): ?>
        <div class="alert alert-warning"><?= app_escape($message) ?></div>
      <?php endif; ?>
      <?php if ($validToken): ?>
        <form method="POST">
          <input type="hidden" name="token" value="<?= app_escape($token) ?>">
          <label class="form-label">New password</label>
          <input type="password" name="password" class="form-control mb-3" required>
          <label class="form-label">Confirm password</label>
          <input type="password" name="confirm_password" class="form-control mb-3" required>
          <button class="btn btn-info">Update password</button>
          <a href="login.php" class="btn btn-outline-light ms-2">Back to login</a>
        </form>
      <?php else: ?>
        <p class="mb-0">The reset link is invalid or expired. You can request a new one from the login page.</p>
        <a href="forgot_password.php" class="btn btn-info mt-3">Request new link</a>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
