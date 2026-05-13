<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

$userId = app_current_user_id();
$gameId = isset($_POST['game_id']) ? (int) $_POST['game_id'] : 0;
$collectionId = isset($_POST['collection_id']) ? (int) $_POST['collection_id'] : 0;

if ($gameId <= 0 || $collectionId <= 0) {
    app_flash('warning', 'Please choose a valid collection.');
    header('Location: collections.php');
    exit();
}

$ownerStmt = $conn->prepare("SELECT id FROM collections WHERE id = ? AND user_id = ? LIMIT 1");
if (!$ownerStmt) {
    app_flash('warning', 'Could not verify the collection.');
    header('Location: collections.php');
    exit();
}

$ownerStmt->bind_param('ii', $collectionId, $userId);
$ownerStmt->execute();
$ownerResult = $ownerStmt->get_result();
$ownerStmt->close();

if (!$ownerResult || $ownerResult->num_rows === 0) {
    app_flash('warning', 'That collection does not belong to your account.');
    header('Location: collections.php');
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO collection_items (collection_id, game_id)
     VALUES (?, ?)
     ON CONFLICT (collection_id, game_id)
     DO UPDATE SET added_at = CURRENT_TIMESTAMP"
);

if ($stmt) {
    $stmt->bind_param('ii', $collectionId, $gameId);
    $stmt->execute();
    $stmt->close();
    app_flash('success', 'Game added to your collection.');
    app_add_notification($conn, $userId, 'Collection updated', 'A game was added to one of your collections.', 'success', 'collections.php');
} else {
    app_flash('warning', 'Could not add the game to that collection.');
}

header('Location: collections.php');
exit();
