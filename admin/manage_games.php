<?php
session_start();
require_once 'db.php';
require_admin();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $game_id = intval($_GET['delete']);
    $stmt = $conn->prepare('SELECT image FROM games WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
    } else {
        $row = null;
    }
    if ($row) {
        $image_path = $row['image'];
        $full_path = dirname(__DIR__) . '/' . str_replace('/', DIRECTORY_SEPARATOR, $image_path);
        if (is_file($full_path)) {
            unlink($full_path);
        }
    }
    $deleteStmt = $conn->prepare('DELETE FROM games WHERE id = ?');
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $game_id);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    header('Location: manage_games.php?msg=deleted');
    exit();
}

$games = $conn->query('SELECT id, title, category, price, image FROM games ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Games</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-5">
    <h2>Manage Games</h2>
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">Game deleted successfully.</div>
    <?php endif; ?>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Image</th>
                <th>Title</th>
                <th>Category</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $games->fetch_assoc()): ?>
            <tr>
                <td><img src="<?= htmlspecialchars($row['image']) ?>" width="80" height="60"></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['category'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['price'] ?? 'N/A') ?></td>
                <td>
                    <a href="manage_games.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this game?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
