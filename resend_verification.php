<?php
require_once "app_helpers.php";
require_once "db.php";

$message = '';
$verificationLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $message = 'Please enter your email address.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, email_verified_at FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($user) {
                if (!empty($user['email_verified_at'])) {
                    $message = 'This account is already verified. You can log in now.';
                } else {
                    $token = app_create_auth_token($conn, (int) $user['id'], 'email_verify', 1440);
                    $verificationLink = "http://localhost/Game_Store/verify_email.php?token=" . urlencode($token);
                    $message = 'Verification link created. Open it to verify your account.';
                    app_add_notification($conn, (int) $user['id'], 'Verification requested', 'A new email verification link was created for your account.', 'info', 'verify_email.php?token=' . urlencode($token));
                }
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
  <title>Resend Verification</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container py-5" style="max-width:720px;">
    <div class="req-card">
      <h1 class="h3 mb-3">Resend Verification</h1>
      <?php if ($message): ?>
        <div class="alert alert-info"><?= app_escape($message) ?></div>
      <?php endif; ?>
      <?php if ($verificationLink): ?>
        <div class="req-card mb-3">
          <div class="small muted mb-2">Verification link</div>
          <a href="<?= app_escape($verificationLink) ?>"><?= app_escape($verificationLink) ?></a>
        </div>
      <?php endif; ?>
      <form method="POST">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control mb-3" required>
        <button class="btn btn-info">Send new link</button>
        <a href="login.php" class="btn btn-outline-light ms-2">Back to login</a>
      </form>
    </div>
  </main>
</body>
</html>
