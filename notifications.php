<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();
$userId = app_current_user_id();

if (isset($_GET['mark_read'])) {
    $nid = (int) $_GET['mark_read'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param('ii', $nid, $userId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: notifications.php');
    exit();
}

if (isset($_GET['mark_all'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: notifications.php');
    exit();
}

$stmt = $conn->prepare(
    "SELECT id, title, message, type, link, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

$unread = app_unread_notification_count($conn, $userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="container py-5" style="max-width: 980px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-1">Notifications</h1>
        <p class="text-secondary mb-0"><?= (int) $unread ?> unread</p>
      </div>
      <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-light">Home</a>
        <a href="notifications.php?mark_all=1" class="btn btn-info">Mark all read</a>
      </div>
    </div>

    <div class="d-grid gap-3">
      <?php if ($notifications): ?>
        <?php foreach ($notifications as $note): ?>
          <div class="req-card <?= $note['is_read'] ? '' : 'border border-info' ?>">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <div class="fw-semibold"><?= app_escape($note['title']) ?></div>
                <div class="text-secondary small mb-2"><?= app_escape($note['created_at']) ?></div>
              </div>
              <span class="badge bg-<?= app_escape($note['type']) ?>"><?= app_escape($note['type']) ?></span>
            </div>
            <p class="mb-3"><?= app_escape($note['message']) ?></p>
            <div class="d-flex gap-2">
              <?php if (!empty($note['link'])): ?>
                <a href="<?= app_escape($note['link']) ?>" class="btn btn-sm btn-outline-light">Open</a>
              <?php endif; ?>
              <?php if (!$note['is_read']): ?>
                <a href="notifications.php?mark_read=<?= (int) $note['id'] ?>" class="btn btn-sm btn-info">Mark read</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="req-card">You do not have any notifications yet.</div>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
