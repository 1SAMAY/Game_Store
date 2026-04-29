<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();
$userId = app_current_user_id();
$flash = app_take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collection_name'])) {
    $name = trim($_POST['collection_name']);
    if ($name === '') {
        app_flash('warning', 'Collection name cannot be empty.');
        header('Location: collections.php');
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO collections (user_id, name) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param('is', $userId, $name);
        $stmt->execute();
        $stmt->close();
        app_flash('success', 'Collection created.');
        app_add_notification($conn, $userId, 'Collection created', 'You created a new collection named ' . $name . '.', 'success', 'collections.php');
    } else {
        app_flash('warning', 'Could not create collection.');
    }

    header('Location: collections.php');
    exit();
}

$collections = [];
$stmt = $conn->prepare("SELECT id, name, created_at FROM collections WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }
    $stmt->close();
}

$itemsByCollection = [];
if ($collections) {
    $collectionIds = array_map(static fn($row) => (int) $row['id'], $collections);
    $in = implode(',', array_fill(0, count($collectionIds), '?'));
    $types = str_repeat('i', count($collectionIds));
    $sql = "SELECT ci.collection_id, g.id AS game_id, g.title, g.image, g.price
            FROM collection_items ci
            JOIN games g ON g.id = ci.game_id
            WHERE ci.collection_id IN ($in)
            ORDER BY ci.added_at DESC";
    $itemStmt = $conn->prepare($sql);
    if ($itemStmt) {
        $itemStmt->bind_param($types, ...$collectionIds);
        $itemStmt->execute();
        $itemResult = $itemStmt->get_result();
        while ($row = $itemResult->fetch_assoc()) {
            $itemsByCollection[(int) $row['collection_id']][] = $row;
        }
        $itemStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Collections</title>
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
        <a href="profile.php" class="btn btn-outline-light rounded-pill">Profile</a>
        <a href="library.php" class="btn btn-outline-light rounded-pill">Library</a>
        <a href="wishlist.php" class="btn btn-outline-light rounded-pill">Wishlist</a>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <?php if ($flash): ?>
      <div class="alert alert-<?= app_escape($flash['type']) ?>"><?= app_escape($flash['message']) ?></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="mb-1">Collections</h1>
        <p class="text-secondary mb-0">Create custom lists like favorites, co-op queue, or backlogs.</p>
      </div>
      <form method="POST" class="d-flex gap-2">
        <input type="text" name="collection_name" class="form-control" placeholder="New collection name" required>
        <button class="btn btn-info">Create</button>
      </form>
    </div>

    <?php if (!$collections): ?>
      <div class="req-card">You do not have any collections yet.</div>
    <?php endif; ?>

    <div class="collection-grid">
      <?php foreach ($collections as $collection): ?>
        <div class="collection-column req-card">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><?= app_escape($collection['name']) ?></h4>
            <span class="small text-secondary"><?= app_escape($collection['created_at']) ?></span>
          </div>

          <?php if (!empty($itemsByCollection[(int) $collection['id']])): ?>
            <?php foreach ($itemsByCollection[(int) $collection['id']] as $item): ?>
              <div class="game-item">
                <img src="<?= app_escape($item['image']) ?>" alt="<?= app_escape($item['title']) ?>">
                <div class="info">
                  <p class="title"><?= app_escape($item['title']) ?></p>
                  <p class="price"><?= app_escape($item['price'] ?? 'Free') ?></p>
                  <div class="d-flex gap-2">
                    <a href="view_game.php?id=<?= (int) $item['game_id'] ?>" class="game-btn text-center">Open</a>
                    <form method="POST" action="remove_from_collection.php">
                      <input type="hidden" name="collection_id" value="<?= (int) $collection['id'] ?>">
                      <input type="hidden" name="game_id" value="<?= (int) $item['game_id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-light">Remove</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-secondary mb-0">This collection is empty.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
</body>
</html>
