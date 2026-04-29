<?php
require_once "app_helpers.php";
require_once "db.php";

$message = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $message = 'Please enter your email address.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($user) {
                $token = app_create_auth_token($conn, (int) $user['id'], 'password_reset', 60);
                $resetLink = "http://localhost/Game_Store/reset_password.php?token=" . urlencode($token);
                $message = 'Password reset link created. Open it to set a new password.';
                app_add_notification($conn, (int) $user['id'], 'Password reset requested', 'A password reset link was created for your account.', 'warning', 'reset_password.php?token=' . urlencode($token));
            } else {
                $message = 'No account found for that email address.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container py-5" style="max-width:720px;">
    <div class="req-card">
      <h1 class="h3 mb-3">Reset Your Password</h1>
      <?php if ($message): ?>
        <div class="alert alert-info"><?= app_escape($message) ?></div>
      <?php endif; ?>
      <?php if ($resetLink): ?>
        <div class="req-card mb-3">
          <div class="small muted mb-2">Reset link</div>
          <a href="<?= app_escape($resetLink) ?>"><?= app_escape($resetLink) ?></a>
        </div>
      <?php endif; ?>
      <form method="POST">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control mb-3" required>
        <button class="btn btn-info">Create reset link</button>
        <a href="login.php" class="btn btn-outline-light ms-2">Back to login</a>
      </form>
    </div>
  </main>
</body>
</html>
