<?php
require_once "app_helpers.php";
require_once "db.php";

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'rating_desc';

$categories = [];
$catResult = $conn->query("SELECT DISTINCT category FROM games WHERE category IS NOT NULL AND category <> '' ORDER BY category");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        foreach (preg_split('/[\/,]+/', $row['category']) as $part) {
            $part = trim($part);
            if ($part !== '') {
                $categories[$part] = true;
            }
        }
    }
}
$categories = array_keys($categories);
sort($categories);

$sql = "SELECT id, title, image, category, price, rating FROM games WHERE 1=1";
$types = '';
$params = [];

if ($search !== '') {
    $sql .= " AND (title LIKE ? OR category LIKE ? OR description LIKE ?)";
    $like = '%' . $search . '%';
    $types .= 'sss';
    array_push($params, $like, $like, $like);
}

if ($category !== '') {
    $sql .= " AND category LIKE ?";
    $types .= 's';
    $params[] = '%' . $category . '%';
}

switch ($sort) {
    case 'title_asc':
        $sql .= " ORDER BY title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY title DESC";
        break;
    case 'rating_asc':
        $sql .= " ORDER BY rating ASC, title ASC";
        break;
    case 'rating_desc':
    default:
        $sql .= " ORDER BY rating DESC, title ASC";
        break;
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Games</title>
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
        <a href="collections.php" class="btn btn-outline-light rounded-pill">Collections</a>
        <a href="profile.php" class="btn btn-outline-light rounded-pill">Profile</a>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <h1 class="mb-2">Browse Games</h1>
        <p class="text-secondary mb-0">Filter by category, search by title, and sort by what matters most.</p>
      </div>
      <form class="d-flex gap-2 flex-wrap" method="GET">
        <input type="search" name="q" class="form-control" placeholder="Search games" value="<?= app_escape($search) ?>">
        <select name="category" class="form-select">
          <option value="">All categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= app_escape($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= app_escape($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="sort" class="form-select">
          <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Top rated</option>
          <option value="rating_asc" <?= $sort === 'rating_asc' ? 'selected' : '' ?>>Lowest rated</option>
          <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A-Z</option>
          <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z-A</option>
        </select>
        <button class="btn btn-info">Apply</button>
      </form>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
      <a class="pill ghost" href="browse.php">All</a>
      <?php foreach ($categories as $cat): ?>
        <a class="pill ghost" href="browse.php?category=<?= urlencode($cat) ?>"><?= app_escape($cat) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="game-row">
      <?php while ($game = $result->fetch_assoc()): ?>
        <div class="game-card">
          <a href="view_game.php?id=<?= (int) $game['id'] ?>">
            <img src="<?= app_escape($game['image']) ?>" alt="<?= app_escape($game['title']) ?>" class="game-card-image">
          </a>
          <div class="game-card-body">
            <p class="card-kicker"><?= app_escape($game['category'] ?: 'Uncategorized') ?></p>
            <p class="card-title"><?= app_escape($game['title']) ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="price mb-0"><?= app_escape($game['price'] ?? 'Free') ?></span>
              <span class="small text-secondary"><?= $game['rating'] !== null ? app_escape(number_format((float) $game['rating'], 1)) . ' ★' : 'No rating' ?></span>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </main>
</body>
</html>
