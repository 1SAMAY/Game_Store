<?php
require_once "app_helpers.php";
require_once "db.php";

app_require_login();

if (isset($_POST['game_id'])) {
    $game_id = intval($_POST['game_id']);
    $user_id = app_current_user_id();

    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    $stmt->close();
    app_flash('success', 'Removed from wishlist.');
    app_add_notification($conn, $user_id, 'Wishlist updated', 'A game was removed from your wishlist.', 'warning', 'wishlist.php');
}
header("Location: wishlist.php");
exit();
?>
