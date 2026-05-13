<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

$userId = app_current_user_id();
$collectionId = isset($_POST['collection_id']) ? (int) $_POST['collection_id'] : 0;
$gameId = isset($_POST['game_id']) ? (int) $_POST['game_id'] : 0;

if ($collectionId > 0 && $gameId > 0) {
    $stmt = $conn->prepare(
        "DELETE FROM collection_items ci
         USING collections c
         WHERE c.id = ci.collection_id
           AND ci.collection_id = ?
           AND ci.game_id = ?
           AND c.user_id = ?"
    );
    if ($stmt) {
        $stmt->bind_param('iii', $collectionId, $gameId, $userId);
        $stmt->execute();
        $stmt->close();
        app_flash('success', 'Removed from collection.');
    }
}

header('Location: collections.php');
exit();
