<?php
require_once "app_helpers.php";
require_once "db.php";

$msg = "";
$verificationLink = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $msg = "Please fill in all fields.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        if ($check) {
            $check->bind_param('ss', $username, $email);
            $check->execute();
            $result = $check->get_result();
            if ($result && $result->num_rows > 0) {
                $msg = "Username or email already exists!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "INSERT INTO users (username, email, password, email_verified_at)
                     VALUES (?, ?, ?, NULL)"
                );
                if ($stmt) {
                    $stmt->bind_param('sss', $username, $email, $hash);
                    if ($stmt->execute()) {
                        $userId = $stmt->insert_id;
                        $token = app_create_auth_token($conn, $userId, 'email_verify', 1440);
                        $verificationLink = "http://localhost/Game_Store/verify_email.php?token=" . urlencode($token);
                        $msg = "Registration successful! Verify your email using the link below.";
                    } else {
                        $msg = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $msg = "Error: " . $conn->error;
                }
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
      min-height: 100vh;
      overflow: hidden;
      font-family: 'Segoe UI', sans-serif;
    }
    .bg-bubbles span {
      position: absolute;
      width: 20px;
      height: 20px;
      background: rgba(255,255,255,0.15);
      border-radius: 50%;
      animation: move 25s linear infinite;
      bottom: -150px;
    }
    .bg-bubbles span:nth-child(1) { left: 10%; animation-duration: 12s; width: 40px; height: 40px; }
    .bg-bubbles span:nth-child(2) { left: 20%; animation-duration: 18s; }
    .bg-bubbles span:nth-child(3) { left: 25%; animation-duration: 22s; width: 50px; height: 50px; }
    .bg-bubbles span:nth-child(4) { left: 40%; animation-duration: 15s; }
    .bg-bubbles span:nth-child(5) { left: 70%; animation-duration: 20s; width: 60px; height: 60px; }
    .bg-bubbles span:nth-child(6) { left: 80%; animation-duration: 17s; }
    .bg-bubbles span:nth-child(7) { left: 90%; animation-duration: 25s; width: 70px; height: 70px; }
    @keyframes move {
      0% { transform: translateY(0); opacity: 0.5; }
      50% { opacity: 1; }
      100% { transform: translateY(-1000px); opacity: 0; }
    }
    .card {
      width: 420px;
      border-radius: 20px;
      background: rgba(40, 40, 40, 0.9);
      box-shadow: 0 8px 20px rgba(0,0,0,0.7);
      backdrop-filter: blur(10px);
      animation: fadeIn 1s ease-in-out;
      position: relative;
      overflow: hidden;
    }
    .card::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(120deg, transparent, rgba(255,255,255,0.2), transparent);
      transform: rotate(25deg);
      animation: shine 3s infinite;
    }
    @keyframes shine {
      0% { transform: translateX(-100%) rotate(25deg); }
      100% { transform: translateX(100%) rotate(25deg); }
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
    input {
      border-radius: 10px !important;
      transition: all 0.3s;
    }
    input:focus {
      box-shadow: 0 0 15px #00ffcc;
      border-color: #00ffcc;
    }
    .btn-glow {
      background: linear-gradient(90deg, #00ffcc, #0077ff);
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 10px;
      transition: 0.3s;
      position: relative;
      overflow: hidden;
    }
    .btn-glow:hover {
      box-shadow: 0 0 25px #00ffcc;
      transform: scale(1.05);
    }
    .code-box {
      word-break: break-all;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      padding: 10px;
      border-radius: 10px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="bg-bubbles">
    <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
  </div>

  <div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="card p-4 text-white">
      <h3 class="mb-3 text-center">Register</h3>
      <?php if ($msg): ?>
        <p class="text-warning text-center mb-2"><?= htmlspecialchars($msg) ?></p>
      <?php endif; ?>
      <?php if ($verificationLink): ?>
        <div class="code-box mb-3">
          <div class="small mb-2">Verification link</div>
          <a href="<?= htmlspecialchars($verificationLink) ?>" class="text-info"><?= htmlspecialchars($verificationLink) ?></a>
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-glow w-100">Register</button>
      </form>
      <p class="mt-3 text-center">Already have an account? <a href="login.php" class="text-info">Login</a></p>
    </div>
  </div>
</body>
</html>
