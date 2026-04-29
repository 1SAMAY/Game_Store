<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();
$userId = app_current_user_id();
$flash = app_take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $displayName = trim($_POST['display_name'] ?? '');
    $avatarUrl = trim($_POST['avatar_url'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $favoriteGenre = trim($_POST['favorite_genre'] ?? '');
    $themePreference = ($_POST['theme_preference'] ?? 'dark') === 'light' ? 'light' : 'dark';

    $stmt = $conn->prepare(
        "UPDATE users
         SET display_name = ?, avatar_url = ?, bio = ?, favorite_genre = ?, theme_preference = ?
         WHERE id = ?"
    );
    if ($stmt) {
        $stmt->bind_param('sssssi', $displayName, $avatarUrl, $bio, $favoriteGenre, $themePreference, $userId);
        $stmt->execute();
        $stmt->close();
        app_set_user_pref_theme($conn, $userId, $themePreference);
        $_SESSION['theme'] = $themePreference;
        app_flash('success', 'Profile updated.');
        app_add_notification($conn, $userId, 'Profile updated', 'Your profile settings were saved.', 'success', 'profile.php');
    } else {
        app_flash('warning', 'Could not update your profile.');
    }

    header('Location: profile.php');
    exit();
}

$stmt = $conn->prepare("SELECT username, email, display_name, avatar_url, bio, favorite_genre, theme_preference FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stats = [
    'library' => (int) ($conn->query("SELECT COUNT(*) AS total FROM library WHERE user_id = {$userId}")->fetch_assoc()['total'] ?? 0),
    'wishlist' => (int) ($conn->query("SELECT COUNT(*) AS total FROM wishlist WHERE user_id = {$userId}")->fetch_assoc()['total'] ?? 0),
    'reviews' => (int) ($conn->query("SELECT COUNT(*) AS total FROM reviews WHERE user_id = {$userId}")->fetch_assoc()['total'] ?? 0),
];

$recentStmt = $conn->prepare(
    "SELECT g.id, g.title, g.image, rv.viewed_at
     FROM recently_viewed rv
     JOIN games g ON g.id = rv.game_id
     WHERE rv.user_id = ?
     ORDER BY rv.viewed_at DESC
     LIMIT 6"
);
$recentStmt->bind_param('i', $userId);
$recentStmt->execute();
$recentResult = $recentStmt->get_result();
$recentGames = [];
while ($row = $recentResult->fetch_assoc()) {
    $recentGames[] = $row;
}
$recentStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <script src="theme.js" defer></script>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: rgba(12, 16, 20, 0.78); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
    <div class="container-fluid px-3 px-lg-4">
      <a href="index.php" class="d-flex align-items-center gap-3 text-decoration-none">
        <img src="https://img.icons8.com/?size=100&id=bCP28brs5BYg&format=png&color=000000" alt="Game Store" class="logo">
        <span class="fw-semibold text-white">Game Store</span>
      </a>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-light rounded-pill" data-theme-toggle>Light mode</button>
        <a href="library.php" class="btn btn-outline-light rounded-pill">Library</a>
        <a href="wishlist.php" class="btn btn-outline-light rounded-pill">Wishlist</a>
        <a href="collections.php" class="btn btn-outline-light rounded-pill">Collections</a>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <?php if ($flash): ?>
      <div class="alert alert-<?= app_escape($flash['type']) ?>"><?= app_escape($flash['message']) ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="req-card">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:72px;height:72px;border-radius:18px;overflow:hidden;background:#222;">
              <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?= app_escape($user['avatar_url']) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div class="d-flex justify-content-center align-items-center h-100 fw-bold"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
              <?php endif; ?>
            </div>
            <div>
              <h1 class="h4 mb-1"><?= app_escape($user['display_name'] ?: $user['username']) ?></h1>
              <div class="text-secondary"><?= app_escape($user['email']) ?></div>
            </div>
          </div>

          <div class="d-flex gap-2 flex-wrap mb-3">
            <span class="pill">Library <?= $stats['library'] ?></span>
            <span class="pill ghost">Wishlist <?= $stats['wishlist'] ?></span>
            <span class="pill ghost">Reviews <?= $stats['reviews'] ?></span>
          </div>

          <form method="POST" class="d-grid gap-3">
            <div>
              <label class="form-label">Display name</label>
              <input type="text" name="display_name" class="form-control" value="<?= app_escape($user['display_name'] ?? '') ?>" placeholder="Display name">
            </div>
            <div>
              <label class="form-label">Avatar URL</label>
              <input type="url" name="avatar_url" class="form-control" value="<?= app_escape($user['avatar_url'] ?? '') ?>" placeholder="https://...">
            </div>
            <div>
              <label class="form-label">Favorite genre</label>
              <input type="text" name="favorite_genre" class="form-control" value="<?= app_escape($user['favorite_genre'] ?? '') ?>" placeholder="Action, Horror, RPG">
            </div>
            <div>
              <label class="form-label">Bio</label>
              <textarea name="bio" class="form-control" rows="4" placeholder="Tell people what you like to play."><?= app_escape($user['bio'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="form-label">Theme preference</label>
              <select name="theme_preference" class="form-select">
                <option value="dark" <?= (($user['theme_preference'] ?? 'dark') === 'dark') ? 'selected' : '' ?>>Dark</option>
                <option value="light" <?= (($user['theme_preference'] ?? 'dark') === 'light') ? 'selected' : '' ?>>Light</option>
              </select>
            </div>
            <button class="btn btn-info">Save Profile</button>
          </form>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="req-card mb-4">
          <h2 class="h4 mb-3">About You</h2>
          <p class="mb-2"><strong>Username:</strong> <?= app_escape($user['username']) ?></p>
          <p class="mb-2"><strong>Favorite genre:</strong> <?= app_escape($user['favorite_genre'] ?: 'Not set') ?></p>
          <p class="mb-0"><strong>Bio:</strong> <?= nl2br(app_escape($user['bio'] ?: 'No bio added yet.')) ?></p>
        </div>

        <div class="req-card">
          <h2 class="h4 mb-3">Recently Viewed</h2>
          <div class="game-row">
            <?php if ($recentGames): ?>
              <?php foreach ($recentGames as $game): ?>
                <div class="game-card">
                  <a href="view_game.php?id=<?= (int) $game['id'] ?>">
                    <img src="<?= app_escape($game['image']) ?>" alt="<?= app_escape($game['title']) ?>" class="game-card-image">
                  </a>
                  <div class="game-card-body">
                    <p class="card-kicker">Viewed <?= app_escape($game['viewed_at']) ?></p>
                    <p class="card-title"><?= app_escape($game['title']) ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-secondary mb-0">No recent games yet.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
