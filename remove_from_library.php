<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_id'])) {
    $game_id = intval($_POST['game_id']);
    $user_id = app_current_user_id();

    $stmt = $conn->prepare("DELETE FROM library WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        app_flash('success', 'Removed from library.');
        app_add_notification($conn, $user_id, 'Library updated', 'A game was removed from your library.', 'warning', 'library.php');
    } else {
        app_flash('warning', 'Game was not found in your library.');
    }

    $stmt->close();
    header("Location: library.php");
    exit;
}
