<?php
session_start();
require_once 'db.php';
require_admin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header('Location: dashboard.php'); exit(); }

$msg = '';
$sql = 'SELECT * FROM games WHERE id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$game) { echo 'Game not found.'; exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $price = $conn->real_escape_string($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $description = $conn->real_escape_string($_POST['description']);
    $media_url = $conn->real_escape_string($_POST['media_url']);
    $min_req = $conn->real_escape_string($_POST['min_requirement']);
    $max_req = $conn->real_escape_string($_POST['max_requirement']);
    $story = $conn->real_escape_string($_POST['story']);
    $rating = floatval($_POST['rating']);

    $imagePath = $game['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
            $rootDir = dirname(__DIR__);
            $publicImageDir = $rootDir . '/images/';
            if (!is_dir($publicImageDir)) {
                mkdir($publicImageDir, 0777, true);
            }
            $imagePath = 'images/' . uniqid('game_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $rootDir . '/' . $imagePath);
        }
    }

    $sql = 'UPDATE games SET title=?, price=?, category=?, description=?, image=?, media_url=?, min_requirement=?, max_requirement=?, story=?, rating=? WHERE id=?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssdii', $title, $price, $category, $description, $imagePath, $media_url, $min_req, $max_req, $story, $rating, $id);
    if ($stmt->execute()) {
        $msg = 'Game updated!';
        header('Location: dashboard.php?msg=updated');
        exit();
    } else {
        $msg = 'Error: ' . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Game</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-5">
    <h2>Edit Game</h2>
    <?php if ($msg): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($game['title']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Price</label>
            <input type="text" name="price" class="form-control" value="<?= htmlspecialchars($game['price']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($game['category']) ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($game['description']) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image</label>
            <?php if ($game['image']): ?>
                <img src="<?= htmlspecialchars($game['image']) ?>" width="80"><br>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Video/Trailer Link</label>
            <input type="text" name="media_url" class="form-control" value="<?= htmlspecialchars($game['media_url']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Min Requirement</label>
            <input type="text" name="min_requirement" class="form-control" value="<?= htmlspecialchars($game['min_requirement']) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Max Requirement</label>
            <input type="text" name="max_requirement" class="form-control" value="<?= htmlspecialchars($game['max_requirement']) ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Story</label>
            <textarea name="story" class="form-control"><?= htmlspecialchars($game['story']) ?></textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">Rating</label>
            <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="<?= htmlspecialchars($game['rating']) ?>">
        </div>
        <div class="col-md-12">
            <button class="btn btn-success">Update Game</button>
        </div>
    </form>
</div>
</body>
</html>