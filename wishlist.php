<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();
$userId = app_current_user_id();

$sql = "SELECT g.*, w.game_id AS wishlist_id
        FROM wishlist w
        JOIN games g ON w.game_id = g.id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$flash = app_take_flash();
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Wishlist</title>
  <script src="theme.js" defer></script>
  <style>
    body {
      background-color: #121212;
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 30px;
      margin: 0;
    }

    h2 {
      text-align: center;
      font-size: 32px;
      margin-bottom: 40px;
    }

    .library-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
    }

    .game-card {
      background-color: #1f1f1f;
      border-radius: 16px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
      overflow: hidden;
      width: 260px;
      transition: transform 0.3s ease;
      position: relative;
    }

    .game-card:hover {
      transform: scale(1.03);
    }

    .game-card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      display: block;
    }

    .game-info {
      padding: 15px;
    }

    .base {
      font-size: 13px;
      color: #a0a0a0;
      margin: 0;
    }

    .game-title {
      font-size: 18px;
      font-weight: bold;
      color: #ffffff;
      margin-top: 5px;
    }

    form {
      position: absolute;
      top: 10px;
      right: 10px;
    }

    .remove-button {
      background-color: transparent;
      border: none;
      color: #fff;
      font-size: 18px;
      cursor: pointer;
      transition: transform 0.3s ease, color 0.3s ease;
    }

    .remove-button:hover {
      color: #ff4d4d;
      transform: scale(1.3) rotate(10deg);
    }

    .library-game-image {
      width: 80px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 15px;
    }
  </style>
</head>
<body>
  <div class="d-flex justify-content-end p-3">
    <button type="button" class="btn btn-outline-light rounded-pill" data-theme-toggle>Light mode</button>
  </div>
  <a href="index.php" style="display: inline-block; margin-bottom: 20px; text-decoration: none;">
    <button style="background-color: #2c2c2c; color: white; border: 1px solid #444; border-radius: 8px; padding: 10px 20px; font-size: 15px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#3d3d3d'" onmouseout="this.style.backgroundColor='#2c2c2c'">← Back to Store</button>
  </a>

  <?php if ($flash): ?>
    <div class="alert alert-<?= app_escape($flash['type']) ?>"><?= app_escape($flash['message']) ?></div>
  <?php endif; ?>

  <h2>My Wishlist</h2>

  <div class="library-container">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($game = $result->fetch_assoc()): ?>
        <div class="game-card">
          <form method="POST" action="remove_from_wishlist.php">
            <!-- Remove by game_id, matching remove_from_wishlist.php -->
            <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
            <button type="submit" class="remove-button" title="Remove">&times;</button>
          </form>
          <img src="<?= htmlspecialchars($game['image']) ?>" class="library-game-image" alt="<?= htmlspecialchars($game['title']) ?>">
          <div class="game-info">
            <p class="base">Base Game</p>
            <p class="game-title"><?= htmlspecialchars($game['title']) ?></p>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="text-align:center;">No games in your wishlist yet.</p>
    <?php endif; ?>
  </div>
</body>
</html>
